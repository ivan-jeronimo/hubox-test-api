<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param array|object $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success(array|object $data = [], string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a failed JSON response.
     *
     * @param string $message
     * @param int $statusCode
     * @param array|object $errors
     * @return JsonResponse
     */
    protected function failed(string $message = 'Failed', int $statusCode = 400, array|object $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return an error JSON response (for unexpected server errors).
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function error(string $message = 'An unexpected error occurred', int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
