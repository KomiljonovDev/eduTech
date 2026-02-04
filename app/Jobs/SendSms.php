<?php

namespace App\Jobs;

use App\Services\EskizSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSms implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Backoff between retries (in seconds).
     *
     * @var array<int>
     */
    public array $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phone,
        public string $message,
        public ?string $callbackUrl = null,
    ) {
        $this->onQueue('sms');
    }

    /**
     * Execute the job.
     */
    public function handle(EskizSmsService $sms): void
    {
        $result = $sms->send($this->phone, $this->message, $this->callbackUrl);

        if (! $result['success']) {
            Log::warning('SMS yuborishda xatolik', [
                'phone' => $this->phone,
                'error' => $result['error'] ?? 'Noma\'lum xato',
            ]);

            $this->fail($result['error'] ?? 'SMS yuborishda xatolik');
        }

        Log::info('SMS muvaffaqiyatli yuborildi', [
            'phone' => $this->phone,
            'response' => $result['data'] ?? null,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return ['sms', 'phone:'.$this->phone];
    }
}
