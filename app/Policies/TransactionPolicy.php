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
 * السياسة مسجّلة صراحةً في AppServiceProvider ولا نعتمد على الاكتشاف
 * التلقائي بالاسم، لأنه يمرّ عبر مُحمِّل الأصناف وقد يفشل صامتًا على سيرفر
 * لم يُحدَّث فيه autoload، فترفض كل العمليات بـ 403 دون سبب ظاهر.
 */
class TransactionPolicy
{
    public function update(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction);
    }

    /**
     * مقارنة المالك بعد توحيد النوع.
     *
     * التحويل إلى int مقصود: user_id ليس المفتاح الأساسي، فلا يحوّله Eloquent
     * تلقائيًا كما يفعل مع id، ويعيده بعض إعدادات PDO/MySQL نصًا "3" بدل 3.
     * عندها تفشل المقارنة الصارمة رغم أن السجل يخص المستخدم فعلًا.
     */
    private function owns(User $user, Transaction $transaction): bool
    {
        return (int) $transaction->user_id === (int) $user->id;
    }
}
