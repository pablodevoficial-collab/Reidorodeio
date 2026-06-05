<?php

use App\Jobs\WarmFantasyRankingCache;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// =====================================================
// CRON SCHEDULE - Shared Hosting Compatible
// =====================================================
// Configure no cPanel/Hostinger: * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1

// Database backup
Schedule::command('backup:database')->dailyAt('03:00');

// Keep fantasy ranking cache warm from persisted snapshots
Schedule::job(new WarmFantasyRankingCache())->everyFiveMinutes();

// =====================================================
// OTIMIZAÇÕES PARA SHARED HOSTING (sem queue workers)
// =====================================================

// Atualiza rankings e aquece cache a cada 5 minutos
Schedule::command('cache:update-rankings --warm')->everyFiveMinutes();

// Aquece cache completo após período de baixo uso (madrugada)
Schedule::command('cache:warmup')->dailyAt('05:00');

// Remove bots de bolões que fecham em menos de 1 hora
Schedule::command('fantasy:cleanup-bots')->everyFiveMinutes();

// Envia alertas por e-mail quando o próximo rodeio entra ao vivo
Schedule::command('rodeio:send-reminders')->everyMinute();
