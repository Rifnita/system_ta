<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif.',
            ], 403);
        }

        $user->tokens()->delete();
        $accessToken = $user->createToken('superadmin-fe')->plainTextToken;

        return response()->json([
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => $this->serializeUser($request->user()),
            ],
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $payload = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'alamat' => ['sometimes', 'nullable', 'string'],
        ]);

        $user->fill($payload);
        $user->save();

        return response()->json([
            'data' => [
                'message' => 'Profil berhasil diperbarui.',
                'user' => $this->serializeUser($user->fresh()),
            ],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $payload = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($payload['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak sesuai.'],
            ]);
        }

        $user->forceFill([
            'password' => $payload['password'],
        ])->save();

        return response()->json([
            'data' => [
                'message' => 'Password berhasil diperbarui.',
            ],
        ]);
    }

    private function serializeUser(?\App\Models\User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'alamat' => $user->alamat,
            'is_active' => (bool) $user->is_active,
            'email_verified_at' => $user->email_verified_at,
            'profile_photo_path' => $user->profile_photo_path,
            'profile_photo_url' => $user->profile_photo_url,
        ];
    }
}
