<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     * @param  Request  $request  Data registrasi dari user
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
     * @param  Request  $request  Data login dari user
     * @return \Illuminate\Http\JsonResponse Response JSON dengan token atau error
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada dan password valid
        if (! $user || ! Hash::check($request->password, $user->password)) {
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
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Get authenticated user information
     *
     * @param  Request  $request  Request dengan token autentikasi
     * @return \Illuminate\Http\JsonResponse Response JSON dengan data user lengkap
     */
    public function user(Request $request)
    {
        $user = $request->user();

        // Load relasi yang dibutuhkan
        $user->loadCount(['products', 'categories', 'transactions']);

        // Tentukan permissions berdasarkan role
        $permissions = $this->getUserPermissions($user->role);

        // Siapkan data komprehensif
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'permissions' => $permissions,
            'statistics' => [
                'total_products' => $user->products_count,
                'total_categories' => $user->categories_count,
                'total_transactions' => $user->transactions_count,
            ],
            'role_description' => $this->getRoleDescription($user->role),
            'api_token_count' => $user->tokens()->count(),
            'last_login' => $user->tokens()->latest()->first()?->last_used_at,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => [
                'user' => $userData,
            ],
        ]);
    }

    /**
     * Get user permissions based on role.
     */
    private function getUserPermissions(?string $role): array
    {
        $permissions = [
            'admin' => [
                'manage_products', 'manage_categories', 'manage_transactions',
                'manage_users', 'view_audit_trails', 'manage_storage',
            ],
            'editor' => [
                'manage_products', 'manage_categories', 'create_transactions',
                'manage_storage',
            ],
            'moderator' => [
                'view_transactions', 'update_transactions', 'view_audit_trails',
                'create_transactions',
            ],
            'user' => [
                'create_transactions', 'view_own_transactions',
            ],
        ];

        // Gunakan 'user' sebagai default jika role null atau tidak dikenal
        $effectiveRole = $role ?? 'user';

        return $permissions[$effectiveRole] ?? $permissions['user'];
    }

    /**
     * Get role description.
     */
    private function getRoleDescription(?string $role): string
    {
        $descriptions = [
            'admin' => 'Full access to all system features and user management',
            'editor' => 'Can manage products, categories, and storage',
            'moderator' => 'Can manage transactions and view audit trails',
            'user' => 'Can create transactions and view own data',
        ];

        // Gunakan 'user' sebagai default jika role null atau tidak dikenal
        $effectiveRole = $role ?? 'user';

        return $descriptions[$effectiveRole] ?? 'Standard user role';
    }

    /**
     * Handle user logout
     *
     * @param  Request  $request  Request dengan token autentikasi
     * @return \Illuminate\Http\JsonResponse Response JSON konfirmasi logout
     */
    public function logout(Request $request)
    {
        // Revoke token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }
}
