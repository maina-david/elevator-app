<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::listen(function (QueryExecuted $query) {
            // Insert query details into the 'query_logs' table
            // DB::table('query_logs')->insert([
            //     'user_id' => auth()->check() ? auth()->id() : NULL,
            //     'query' => $query->sql,
            //     'query_time' => $query->time,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);
        });
    }
}