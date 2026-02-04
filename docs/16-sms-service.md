# SMS Xizmati

Eskiz SMS provayderi orqali SMS yuborish.

## Fayllar

- **Service**: `app/Services/EskizSmsService.php`
- **Job**: `app/Jobs/SendSms.php`
- **Command**: `app/Console/Commands/SendTestSms.php`

## Eskiz SMS Service

### Konfiguratsiya

```env
# .env
ESKIZ_EMAIL=your@email.com
ESKIZ_PASSWORD=your_password
ESKIZ_CALLBACK_URL=https://your-domain.com/sms/callback
```

### Service Kodi

```php
// app/Services/EskizSmsService.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EskizSmsService
{
    private string $baseUrl = 'https://notify.eskiz.uz/api';
    private string $email;
    private string $password;
    private ?string $callbackUrl;

    public function __construct()
    {
        $this->email = config('services.eskiz.email');
        $this->password = config('services.eskiz.password');
        $this->callbackUrl = config('services.eskiz.callback_url');
    }

    /**
     * SMS yuborish
     */
    public function send(string $phone, string $message): array
    {
        $phone = $this->normalizePhone($phone);
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/message/sms/send', [
                'mobile_phone' => $phone,
                'message' => $message,
                'from' => '4546',
                'callback_url' => $this->callbackUrl,
            ]);

        return $response->json();
    }

    /**
     * Token olish (cached)
     */
    private function getToken(): string
    {
        return Cache::remember('eskiz_token', 3600, function () {
            $response = Http::post($this->baseUrl . '/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            return $response->json('data.token');
        });
    }

    /**
     * Telefon raqamini normalizatsiya qilish
     */
    private function normalizePhone(string $phone): string
    {
        // Faqat raqamlarni qoldirish
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // 998 bilan boshlanmasa, qo'shish
        if (!str_starts_with($phone, '998')) {
            $phone = '998' . $phone;
        }

        return $phone;
    }
}
```

## SendSms Job

Queue orqali asinxron yuborish.

```php
// app/Jobs/SendSms.php
namespace App\Jobs;

use App\Services\EskizSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public string $phone,
        public string $message
    ) {
        $this->onQueue('sms');
    }

    public function handle(EskizSmsService $smsService): void
    {
        Log::info('SMS yuborilmoqda', [
            'phone' => $this->phone,
            'message' => $this->message,
        ]);

        $result = $smsService->send($this->phone, $this->message);

        Log::info('SMS natijasi', $result);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SMS yuborishda xato', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

## Test Command

```php
// app/Console/Commands/SendTestSms.php
namespace App\Console\Commands;

use App\Jobs\SendSms;
use Illuminate\Console\Command;

class SendTestSms extends Command
{
    protected $signature = 'sms:test {phone}';
    protected $description = 'Test SMS yuborish';

    public function handle(): void
    {
        $phone = $this->argument('phone');

        SendSms::dispatch($phone, 'Bu test xabari. EduTech tizimidan.');

        $this->info("SMS {$phone} ga yuborildi (queue orqali)");
    }
}
```

## Ishlatilish Misollari

### To'g'ridan-to'g'ri yuborish

```php
use App\Services\EskizSmsService;

$smsService = app(EskizSmsService::class);
$result = $smsService->send('+998901234567', 'Salom, bu xabar!');
```

### Queue orqali (tavsiya etiladi)

```php
use App\Jobs\SendSms;

// Oddiy yuborish
SendSms::dispatch('+998901234567', 'Xabar matni');

// Kechiktirilgan yuborish
SendSms::dispatch('+998901234567', 'Xabar matni')
    ->delay(now()->addMinutes(5));
```

### Artisan orqali test

```bash
php artisan sms:test 998901234567
```

## SMS Shablonlari

### To'lov eslatmasi

```php
$message = "Hurmatli {$student->name}! {$group->name} guruhidagi to'lovingiz muddati yaqinlashmoqda. Summa: " . number_format($amount, 0, '', ' ') . " so'm. EduTech";

SendSms::dispatch($student->phone, $message);
```

### Dars eslatmasi

```php
$message = "Eslatma: Bugun soat {$group->start_time} da {$group->course->name} darsi. Xona: {$group->room->name}. EduTech";

SendSms::dispatch($student->phone, $message);
```

### Yangi guruh xabari

```php
$message = "Hurmatli {$student->name}! Siz {$group->name} guruhiga qo'shildingiz. Darslar: " . ($group->days === 'odd' ? 'Du/Chor/Ju' : 'Se/Pay/Sha') . " {$group->start_time}. EduTech";

SendSms::dispatch($student->phone, $message);
```

## Queue Konfiguratsiyasi

```php
// config/queue.php
'connections' => [
    'sms' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'sms',
        'retry_after' => 90,
    ],
],
```

### Queue Worker

```bash
# SMS queue worker
php artisan queue:work --queue=sms

# Laravel Horizon bilan
php artisan horizon
```

## Eskiz API Response

### Muvaffaqiyatli

```json
{
    "id": "123456",
    "status": "waiting",
    "message": "Waiting for SMS provider"
}
```

### Xato

```json
{
    "status": "error",
    "message": "Invalid phone number"
}
```

## Xavfsizlik

### Rate Limiting

```php
// AppServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('sms', function ($job) {
    return Limit::perMinute(10)->by($job->phone);
});
```

### Telefon validatsiyasi

```php
// Faqat O'zbekiston raqamlari
$validated = preg_match('/^998[0-9]{9}$/', $phone);
```

## Service Provider

```php
// config/services.php
'eskiz' => [
    'email' => env('ESKIZ_EMAIL'),
    'password' => env('ESKIZ_PASSWORD'),
    'callback_url' => env('ESKIZ_CALLBACK_URL'),
],
```

## Bog'liq Modullar

- [Talabalar](./03-students.md) - Talabaga SMS yuborish
- [Guruhlar](./04-groups.md) - Dars eslatmalari
- [To'lovlar](./06-payments.md) - To'lov eslatmalari
