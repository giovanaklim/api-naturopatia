<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiExceptionManager;
use App\Helpers\Response;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function isAuth(): JsonResponse
    {
        try {
            $response = Auth::check() ? Auth::user() : throw new \Exception("Usuário não autenticado", 401);

            return Response::getJsonResponse('success', $response, 200);
        } catch (\Exception $e) {
            return ApiExceptionManager::handleException($e, $e->getCode());
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $response = [];
        if (Auth::attempt($request->toArray())) {
            $request->session()->regenerate();

            $response = Response::getJsonResponse('success', Auth::user(), 200);
        } else {
            $response = Response::getJsonResponse('Email/Senha não encontrado.', [], 401);
        }

        return $response;
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Response::getJsonResponse('success', [], 200);
    }
}
