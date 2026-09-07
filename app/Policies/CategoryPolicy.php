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
        return $this->owns($user, $category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->owns($user, $category);
    }

    /**
     * مقارنة المالك بعد توحيد النوع — للسبب نفسه الموضّح في TransactionPolicy.
     */
    private function owns(User $user, Category $category): bool
    {
        return (int) $category->user_id === (int) $user->id;
    }
}
