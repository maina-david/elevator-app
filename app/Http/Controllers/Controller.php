<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Send a success response.
     *
     * @param string|null $message
     * @param mixed|null $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function success(string $message = null, mixed $data = null, int $status = 200, array $headers = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return new JsonResponse($response, $status, $headers);
    }

    /**
     * Send an error response.
     *
     * @param string|null $message
     * @param mixed|null $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function error(string $message = null, mixed $data = null, int $status = 400, array $headers = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        return new JsonResponse($response, $status, $headers);
    }
}