<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Le decimos a Laravel cómo pluralizar "filial" en español
        Str::macro('pluralStudly', fn($value) => $value);
    }
}
