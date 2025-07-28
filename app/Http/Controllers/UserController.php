<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->withCount(['products', 'categories', 'transactions'])
            ->paginate($request->per_page ?? 15);

        // Transform data untuk response yang lebih komprehensif
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'statistics' => [
                    'total_products' => $user->products_count,
                    'total_categories' => $user->categories_count,
                    'total_transactions' => $user->transactions_count,
                ],
                'role_description' => $this->getRoleDescription($user->role),
            ];
        });

        return response()->json([
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

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
        ];

        return response()->json([
            'data' => $userData,
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => 'sometimes|string|in:admin,user,editor,moderator',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'role']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'data' => $user,
            'message' => 'User updated successfully'
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
                'manage_users', 'view_audit_trails', 'manage_storage'
            ],
            'editor' => [
                'manage_products', 'manage_categories', 'create_transactions',
                'manage_storage'
            ],
            'moderator' => [
                'view_transactions', 'update_transactions', 'view_audit_trails',
                'create_transactions'
            ],
            'user' => [
                'create_transactions', 'view_own_transactions'
            ]
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
            'user' => 'Can create transactions and view own data'
        ];

        // Gunakan 'user' sebagai default jika role null atau tidak dikenal
        $effectiveRole = $role ?? 'user';
        return $descriptions[$effectiveRole] ?? $descriptions['user'];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Change user role.
     */
    public function changeRole(Request $request, User $user)
    {
        $this->authorize('changeRole', $user);

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,user,editor,moderator',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'data' => $user,
            'message' => 'User role updated successfully'
        ]);
    }
}