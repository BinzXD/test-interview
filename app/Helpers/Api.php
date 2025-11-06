<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class Api
{
    public static function send($data, $statusCode = 200)
    {
        $statusCode = is_numeric($statusCode) ? (int) $statusCode : 500;

        $isSuccess = !isset($data['errors']) && $statusCode === 200;
        $metadata = [];

        if ($data instanceof LengthAwarePaginator) {
            $metadata = [
                'per_page'     => (int) $data->perPage(),
                'current_page' => (int) $data->currentPage(),
                'total_row'    => (int) $data->total(),
                'total_page'   => (int) $data->lastPage(),
            ];

            $data = $data->items();
        }

        return response()->json([
            'success'  => $isSuccess,
            'message'  => $isSuccess ? 'Success' : 'Failed',
            'errors'   => $isSuccess ? null : [
                'code'    => $data['errors']['code'] ?? $statusCode,
                'message' => $data['errors']['message'] ?? 'Server Error',
            ],
            'metadata' => $metadata,
            'data'     => $isSuccess ? ($data['results'] ?? $data) : null,
        ], $statusCode);
    }
}
