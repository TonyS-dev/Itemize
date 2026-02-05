<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // any authenticated user can view the index (scoping is handled in controller)
        return true;
    }

    public function view(User $user, Product $product)
    {
        return $user->isAdmin() || $user->id === $product->user_id;
    }

    public function create(User $user)
    {
        return true; // any authenticated user can create
    }

    public function update(User $user, Product $product)
    {
        return $user->isAdmin() || $user->id === $product->user_id;
    }

    public function delete(User $user, Product $product)
    {
        return $user->isAdmin() || $user->id === $product->user_id;
    }
}
