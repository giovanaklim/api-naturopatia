<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class Response
{
    /**
     * Returns a JSON response for the current request
     *
     * @param string $message The message for the response
     * @param mixed $data The data contained in the response
     * @param int $code The HTTP code for the response
     * @param string $requestId Optional request identifier that can be forced for logging reasons
     * @param array $errorData
     * @param array $errorList
     * @return JsonResponse
     */
    public static function getJsonResponse($message, $data, $code, $requestId = '', $errorData = [], $errorList = []): JsonResponse
    {
        self::logError($code, $data);
        $requestId = !empty($requestId) ? $requestId : uniqid();

        $jsonResponseData = [
            'message' => __('messages.' . $message),
            'data' => $data,
            'status' => $code,
            'request_id' => $requestId
        ];

        if (!empty($errorData)) {
            $jsonResponseData['error_data'] = $errorData;
        }

        if (!empty($errorList)) {
            $jsonResponseData['errors'] = $errorList['errors'];
        }

        return response()->json($jsonResponseData, $code);
    }

    /**
     * A simple Logging method based on specific HTTP return codes
     *
     * @param int $code The HTTP code used
     * @param string $data The data that should be logged alongside it
     *
     * @return void
     */
    private static function logError($code, $data)
    {
        if ($code === 422 || $code === 401) {
            Log::info($data);
        }
    }
}
