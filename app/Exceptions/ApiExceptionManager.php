<?php

namespace App\Exceptions;

use App\Helpers\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class ApiExceptionManager
{
    /**
     * Handles the specific API errors and logs them
     *
     * @param Exception $exception
     * @param $data
     *
     * @return JsonResponse
     */
    public static function handleException($exception, $data, $customMessage = null, $code = null): JsonResponse
    {
        $customMessage = empty($customMessage) ? 'internal_error' : $customMessage;
        $userUuid = request()->user() !== null ? request()->user()->uuid : 'not_logged';
        $requestId = uniqid();

        $routeParameters = request()->route()->parameters();
        $requestParameters = request()->all();

        foreach ($requestParameters as $requestParameterName => &$requestParameterValue) {
            if (is_object($requestParameterValue)) {
                $requestParameterValue = '<object>';
            }
        }

        $exceptionCode = $code ?? self::getExceptionCode($exception);

        //error 422 should not be added to the log file since it is a missing fields error
        if ($exceptionCode != 422) {
            Log::error(
                'requestId => ' . $requestId
                . ' || routeAction => ' . Route::currentRouteAction()
                . ' || currentUrl => ' . url()->current()
                . ' || exceptionType => ' . get_class($exception)
                . ' || statusCode => ' . $exceptionCode
                . ' || userUUID => ' . $userUuid
                . ' || exceptionMessage => ' . $exception->getMessage()
                . ' || fileLine => ' . $exception->getLine()
                . ' || fileName => ' . $exception->getFile()
                . ' || methodData => ' . json_encode($data)
                . ' || routeParameters => ' . json_encode($routeParameters)
                . ' || requestParameters => ' . json_encode($requestParameters)
            );
        }

        $errorData = [];
        if (env('APP_DEBUG', false) === true) {
            $errorData = [
                'routeAction' => Route::currentRouteAction(),
                'currentUrl' => url()->current(),
                'exceptionType' => get_class($exception),
                'user.uuid' => $userUuid,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'method_data' => json_encode($data),
                'route_parameters' => json_encode($routeParameters),
                'request_parameters' => json_encode($requestParameters),
                'status_code' => $exceptionCode,
            ];
        }

        switch (get_class($exception)) {
            case "Illuminate\Database\Eloquent\ModelNotFoundException":
                return Response::getJsonResponse('record_not_found', [], $exceptionCode, $requestId, $errorData);
            case "Illuminate\Http\Exceptions\HttpResponseException":
                return Response::getJsonResponse('missing_fields', [], $exceptionCode, $requestId, $errorData, $data);
            default:
                break;
        }

        return Response::getJsonResponse($customMessage, [], $exceptionCode, $requestId, $errorData);
    }

    private static function getExceptionCode($exception)
    {
        $exceptionCode = 500;
        switch (get_class($exception)) {
            case "Illuminate\Database\Eloquent\ModelNotFoundException":
                $exceptionCode = 404;
                break;
            case "Illuminate\Http\Exceptions\HttpResponseException":
                $exceptionCode = 422;
                break;
        }

        return $exceptionCode;
    }
}
