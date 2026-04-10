<?php

declare(strict_types=1);

namespace App\Domain\Auth\Http\Controllers;

use App\Domain\Auth\Http\Requests\LoginRequest;
use App\Domain\Auth\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 處理註冊請求
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'success' => true,
            'token' => $user->createToken($data['device_name'])->plainTextToken,
            'user' => $user,
        ]);
    }

    /**
     * 處理登入請求
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['提供的登入資訊不正確。'],
            ]);
        }

        return response()->json([
            'success' => true,
            'token' => $user->createToken($data['device_name'])->plainTextToken,
            'user' => $user,
        ]);
    }

    /**
     * 處理登出請求
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => '已成功登出',
        ]);
    }
}
