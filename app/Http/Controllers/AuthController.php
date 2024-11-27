<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // Validasi input
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Coba autentikasi
            if (!($token = JWTAuth::attempt($credentials))) {
                return response()->json(
                    [
                        'message' => 'email or password incorrect!',
                    ],
                    401,
                );
            }

            // Dapatkan user yang login
            $user = auth()->user();

            return response()->json([
                'message' => 'success',
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'failed to login!',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    // User logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'success',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        try {
            $user = User::findOrFail($user->id);

            // Validation input
            $request->validate([
                'name' => 'required|string|unique:users,name,' . $user->id . '|max:255',
                'phone' => 'nullable|string|max:13',
                'address' => 'required|string',
                'email' => 'nullable|email|unique:users,email,' . $user->id . '|max:255',
                'password' => 'nullable|string|min:6',
            ]);

            // Update fields
            $user->update([
                'name' => $request->name ?? $user->name,
                'phone' => $request->phone ?? $user->phone,
                'address' => $request->address ?? $user->address,
                'email' => $request->email ?? $user->email,
                'password' => $request->has('password') ? Hash::make($request->password) : $user->password,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $user,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle error if user not found
            return response()->json(
                [
                    'message' => 'User not found!',
                ],
                404,
            );
        } catch (\Exception $e) {
            // Handle any error
            return response()->json(
                [
                    'message' => 'Profile update failed!',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
