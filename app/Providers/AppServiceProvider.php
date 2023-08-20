<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $message = auth()->check() ? 'Query executed by User: ' . auth()->id() : 'Query executed: ';

        DB::listen(function (QueryExecuted $query) use ($message) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/DB-SQL-queries-executed.log'),
            ])->alert($message, [
                'query' => $query->sql,
                'query-bindings' =>  $query->bindings,
                'query-time' => $query->time
            ]);
        });
    }
}
