<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Config;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

try {
    $config = Config::first();
    if ($config && $config->scheduler_active && $config->scheduler_interval > 0) {
        Schedule::job(new \App\Jobs\SyncDraftDocuments())->cron("*/{$config->scheduler_interval} * * * *");
    }
} catch (\Exception $e) {
    // Suppress errors during migration or if DB doesn't exist yet
}
