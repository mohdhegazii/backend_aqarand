<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\NormalizeMediaPaths;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the normalize command manually if not autoloaded
Artisan::starting(function ($artisan) {
    $artisan->resolve(NormalizeMediaPaths::class);
});
