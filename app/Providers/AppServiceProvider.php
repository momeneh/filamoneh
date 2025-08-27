<?php

namespace App\Providers;

use App\Models\User;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::$defaultDateDisplayFormat = 'Y/m/d';
        Table::$defaultDateTimeDisplayFormat = 'Y/m/d H:i';
       
        // DB::Listen(function($query){
        //     Log::debug($query->sql);
        //     Log::debug($query->bindings);
        // });       
    }
}

