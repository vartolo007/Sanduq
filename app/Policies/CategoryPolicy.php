<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * صلاحيات التصنيفات — البند 4.3.
 */
class CategoryPolicy
{
    public function update(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }
}
