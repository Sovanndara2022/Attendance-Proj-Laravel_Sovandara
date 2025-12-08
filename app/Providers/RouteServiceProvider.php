<?php

namespace App\Providers;

use App\Http\Middleware\EnsureTeacher;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::aliasMiddleware('teacher', EnsureTeacher::class);
    }
}
