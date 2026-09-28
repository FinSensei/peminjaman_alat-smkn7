<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('peminjaman:tandai-telat')->dailyAt('00:30');
Schedule::command('peminjaman:auto-cancel-req-kembali')->hourly();
