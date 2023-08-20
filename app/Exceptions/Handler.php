<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Handling UnauthorizedHttpException (401)
        $this->renderable(function (UnauthorizedHttpException $e, Request $request) {
            return response()->json([
                'message' => 'Unauthorized access.',
            ], 401);
        });

        // Handling AuthenticationException (401)
        $this->renderable(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'message' => 'Unauthorized access.',
            ], 401);
        });

        // Handling NotFoundHttpException (404)
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        });

        // Handling ModelNotFoundException (404)
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'message' => 'Record not found.',
            ], 404);
        });

        //Handling MethodNotAllowedHttpException(405)
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json([
                'status' => 405,
                'message' => 'Method Not Allowed',
            ], 405);
        });
    }
}