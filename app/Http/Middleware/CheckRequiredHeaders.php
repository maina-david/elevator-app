<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRequiredHeaders
{
    /**
     * The required headers that should be present in the request.
     *
     * @var array
     */
    protected $requiredHeaders = [
        // 'Accept',
        // 'Content-Type',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($this->requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return response()->json([
                    'message' => 'Required header ' . $header . ' not present.',
                ], 400);
            }
        }
        return $next($request);
    }
}