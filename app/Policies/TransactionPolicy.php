<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin' ||
               $user->role === 'moderator' ||
               $transaction->user_id === $user->id;
    }

    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'user', 'editor']);
    }

    /**
     * Determine whether the user can update the transaction.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin' ||
               ($user->role === 'moderator' && $transaction->user_id !== $user->id) ||
               ($transaction->user_id === $user->id && $transaction->status === 'pending');
    }

    /**
     * Determine whether the user can delete the transaction.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin' ||
               ($transaction->user_id === $user->id && $transaction->status === 'pending');
    }

    /**
     * Determine whether the user can restore the transaction.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the transaction.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view their own transactions.
     */
    public function viewOwn(User $user): bool
    {
        return in_array($user->role, ['user', 'editor', 'moderator', 'admin']);
    }
}
