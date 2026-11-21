<?php

use App\Services\FeeReminderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fees:send-due-reminders {--days=0 : Include installments due within this many days}', function (FeeReminderService $service) {
    $result = $service->sendDueReminders((int) $this->option('days'));

    $this->info(sprintf(
        'Due reminders: %d checked, %d SMS sent, %d failed, %d skipped.',
        $result['total'],
        $result['sent'],
        $result['failed'],
        $result['skipped']
    ));
})->purpose('Send automated SMS fee-due reminders and publish portal dues');

Schedule::command('fees:send-due-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();
