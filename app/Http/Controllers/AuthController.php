<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Controller untuk menangani autentikasi pengguna
 * Menyediakan endpoint login dan logout
 */
class AuthController extends Controller
{
    /**
     * Handle user registration
     * 
     * @param Request $request Data registrasi dari user
     * @return \Illuminate\Http\JsonResponse Response JSON dengan token atau error
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Handle user login
     * 
     * @param Request $request Data login dari user
     * @return \Illuminate\Http\JsonResponse Response JSON dengan token atau error
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada dan password valid
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Buat token untuk user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response dengan token
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Handle user logout
     * 
     * @param Request $request Request dengan token autentikasi
     * @return \Illuminate\Http\JsonResponse Response JSON konfirmasi logout
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    /**
     * Handle user logout
     * 
     * @param Request $request Request dengan token autentikasi
     * @return \Illuminate\Http\JsonResponse Response JSON konfirmasi logout
     */
    public function logout(Request $request)
    {
        // Revoke token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}