<?php

namespace App\Console\Commands;

use App\Services\EskizSmsService;
use Illuminate\Console\Command;

class SendTestSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone : Telefon raqami (998XXXXXXXXX)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eskiz orqali test SMS yuborish';

    /**
     * Execute the console command.
     */
    public function handle(EskizSmsService $sms): int
    {
        $phone = $this->argument('phone');

        $this->info("SMS yuborilmoqda: {$phone}");

        // Test rejimida faqat shu tekstlar ishlaydi
        $result = $sms->send($phone, 'Bu Eskiz dan test');

        if ($result['success']) {
            $this->info('SMS muvaffaqiyatli yuborildi!');
            $this->table(
                ['Field', 'Value'],
                collect($result['data'])->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])->values()
            );

            return Command::SUCCESS;
        }

        $this->error('SMS yuborishda xatolik: '.($result['error'] ?? 'Noma\'lum xato'));

        return Command::FAILURE;
    }
}
