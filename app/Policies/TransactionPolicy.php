<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/**
 * صلاحيات الحركات المالية — البند 4.3: كل مستخدم يصل إلى بياناته فقط.
 *
 * بدون هذه السياسة يستطيع أي مستخدم فتح /transactions/5/edit وتعديل حركة
 * لا تخصّه، لأن الرابط لا يحمل سوى رقم السجل.
 *
 * Laravel يكتشف هذا الملف تلقائيًا لأن اسمه يطابق النموذج Transaction.
 */
class TransactionPolicy
{
    public function update(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }
}
