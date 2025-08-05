<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Transaction::class => TransactionPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define role-based gates
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('editor', function (User $user) {
            return in_array($user->role, ['admin', 'editor']);
        });

        Gate::define('moderator', function (User $user) {
            return in_array($user->role, ['admin', 'moderator']);
        });

        Gate::define('user', function (User $user) {
            return in_array($user->role, ['admin', 'user', 'editor', 'moderator']);
        });

        // Specific permission gates
        Gate::define('manage-products', function (User $user) {
            return in_array($user->role, ['admin', 'editor']);
        });

        Gate::define('manage-categories', function (User $user) {
            return in_array($user->role, ['admin', 'editor']);
        });

        Gate::define('manage-transactions', function (User $user) {
            return in_array($user->role, ['admin', 'moderator']);
        });

        Gate::define('manage-users', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('view-audit-trails', function (User $user) {
            return in_array($user->role, ['admin', 'moderator']);
        });
    }
}
