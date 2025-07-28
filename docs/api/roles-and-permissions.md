# Roles and Permissions System

## Overview

This Laravel application implements a comprehensive role-based access control (RBAC) system using Laravel Policies, Gates, and custom middleware. The system provides granular permissions based on user roles.

## Roles

The system supports four distinct roles with different permission levels:

### 1. Admin
- **Full system access**
- Can manage all resources (products, categories, transactions, users)
- Can view audit trails
- Can change user roles
- Can restore soft-deleted items

### 2. Editor
- **Content management**
- Can manage products and categories
- Can create and update their own content
- Cannot manage users or view audit trails
- Cannot delete transactions

### 3. Moderator
- **Transaction oversight**
- Can view all transactions
- Can manage transactions (update status, etc.)
- Can view audit trails
- Cannot manage products, categories, or users

### 4. User
- **Basic user access**
- Can view products and categories
- Can create their own transactions
- Can view their own transactions
- Can view their own audit trails
- Cannot manage other users' content

## Permissions Matrix

| Action | Admin | Editor | Moderator | User |
|--------|-------|--------|-----------|------|
| **Products** |  |  |  |  |
| View Products | ✅ | ✅ | ✅ | ✅ |
| Create Products | ✅ | ✅ | ❌ | ❌ |
| Update Products | ✅ | ✅ | ❌ | ❌ |
| Delete Products | ✅ | ✅ | ❌ | ❌ |
| **Categories** |  |  |  |  |
| View Categories | ✅ | ✅ | ✅ | ✅ |
| Create Categories | ✅ | ✅ | ❌ | ❌ |
| Update Categories | ✅ | ✅ | ❌ | ❌ |
| Delete Categories | ✅ | ✅ | ❌ | ❌ |
| **Transactions** |  |  |  |  |
| View All Transactions | ✅ | ❌ | ✅ | ❌ |
| View Own Transactions | ✅ | ✅ | ✅ | ✅ |
| Create Transactions | ✅ | ✅ | ✅ | ✅ |
| Update Transactions | ✅ | ❌ | ✅ | ❌ |
| Delete Transactions | ✅ | ❌ | ✅ | ❌ |
| **Users** |  |  |  |  |
| View All Users | ✅ | ❌ | ❌ | ❌ |
| Update Users | ✅ | ❌ | ❌ | ✅ (own) |
| Delete Users | ✅ | ❌ | ❌ | ❌ |
| Change User Roles | ✅ | ❌ | ❌ | ❌ |
| **Audit Trails** |  |  |  |  |
| View All Audit Trails | ✅ | ❌ | ✅ | ❌ |
| View Own Audit Trails | ✅ | ✅ | ✅ | ✅ |
| **Storage** |  |  |  |  |
| Upload Files | ✅ | ✅ | ❌ | ❌ |
| Delete Files | ✅ | ✅ | ❌ | ❌ |
| View Files | ✅ | ✅ | ✅ | ✅ |

## API Endpoints and Permissions

### Authentication
- `POST /api/register` - Public (no authentication required)
- `POST /api/login` - Public (no authentication required)
- `GET /api/user` - Authenticated users only
- `POST /api/logout` - Authenticated users only

### Products
- `GET /api/products` - All roles
- `GET /api/products/{id}` - All roles
- `POST /api/products` - Admin, Editor
- `PUT /api/products/{id}` - Admin, Editor
- `DELETE /api/products/{id}` - Admin, Editor

### Categories
- `GET /api/categories` - All roles
- `GET /api/categories/{id}` - All roles
- `POST /api/categories` - Admin, Editor
- `PUT /api/categories/{id}` - Admin, Editor
- `DELETE /api/categories/{id}` - Admin, Editor

### Transactions
- `GET /api/transactions` - Admin, Moderator
- `GET /api/transactions/{id}` - Admin, Moderator
- `POST /api/transactions` - All authenticated users
- `PUT /api/transactions/{id}` - Admin, Moderator
- `DELETE /api/transactions/{id}` - Admin, Moderator
- `GET /api/my-transactions` - All authenticated users (own transactions)

### Users
- `GET /api/users` - Admin only
- `GET /api/users/{id}` - Admin only
- `PUT /api/users/{id}` - Admin (all users), Users (own profile)
- `DELETE /api/users/{id}` - Admin only

### Audit Trails
- `GET /api/audit-trails` - Admin, Moderator
- `GET /api/audit-trails/{id}` - Admin, Moderator
- `GET /api/audit-trails/model/{type}/{id}` - Admin, Moderator
- `GET /api/my-audit-trails` - All authenticated users

### Storage
- `GET /api/storage` - Admin, Editor
- `POST /api/storage` - Admin, Editor
- `GET /api/storage/{filename}` - All authenticated users
- `DELETE /api/storage/{filename}` - Admin, Editor

## Usage Examples

### Setting User Role
```php
$user = User::find(1);
$user->role = 'admin'; // or 'editor', 'moderator', 'user'
$user->save();
```

### Checking Permissions in Controllers
```php
// Check if user can update a product
$this->authorize('update', $product);

// Check role using gates
if (Gate::allows('admin')) {
    // Admin-specific logic
}
```

### Checking Permissions in Routes
```php
// Using middleware
Route::middleware('role:admin,editor')->group(function () {
    // Routes for admin and editor only
});
```

## Testing Accounts

After running the seeder, you can test with these accounts:

- **Admin**: admin@example.com / password
- **Editor**: editor@example.com / password
- **Moderator**: moderator@example.com / password
- **User**: user@example.com / password

## Error Responses

When a user doesn't have sufficient permissions, the API will return:

```json
{
    "message": "Forbidden. Insufficient permissions.",
    "required_roles": ["admin", "editor"],
    "your_role": "user"
}
```

## Implementation Details

### Files Created/Modified
- `app/Policies/ProductPolicy.php` - Product permissions
- `app/Policies/CategoryPolicy.php` - Category permissions
- `app/Policies/TransactionPolicy.php` - Transaction permissions
- `app/Policies/UserPolicy.php` - User management permissions
- `app/Http/Middleware/RoleMiddleware.php` - Role-based middleware
- `app/Http/Controllers/UserController.php` - User management endpoints
- `database/migrations/2025_07_28_140000_add_role_to_users_table.php` - Add role column
- `database/seeders/RoleSeeder.php` - Seed test users with roles
- `app/Providers/AppServiceProvider.php` - Register policies and gates

### Middleware Registration
The RoleMiddleware is automatically registered when using the `role:` prefix in routes.

### Policy Registration
All policies are registered in `AppServiceProvider` using the `$policies` array.