<?php

namespace App\Console\Commands;

use App\Services\RodeioEmailReminderService;
use Illuminate\Console\Command;

class SendRodeioReminderEmails extends Command
{
    protected $signature = 'rodeio:send-reminders';

    protected $description = 'Envia e-mails automáticos para usuários inscritos no início do rodeio';

    public function handle(RodeioEmailReminderService $service): int
    {
        $sentCount = $service->sendLiveStartedNotifications();

        $this->info(sprintf('Alertas de início enviados: %d', $sentCount));

        return self::SUCCESS;
    }
}
