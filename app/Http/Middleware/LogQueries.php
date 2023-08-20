<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LogQueries
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Continue processing the request and get the response
        $response = $next($request);

        // Check if the 'db' (database) service is registered in the container
        if (app()->bound('db')) {
            // Retrieve the executed queries from the query log
            $queries = DB::getQueryLog();

            // Iterate through each query in the query log
            foreach ($queries as $query) {
                // Insert query details into the 'query_logs' table
                DB::table('query_logs')->insert([
                    'user_id' => auth()->check() ? auth()->id() : NULL,
                    'ip_address' => $request->ip(),
                    'query' => $query['query'],         // The SQL query itself
                    'created_at' => now(),              // Timestamp of log creation
                    'updated_at' => now(),              // Timestamp of log update
                ]);
            }
        }

        // Return the response
        return $response;
    }
}
