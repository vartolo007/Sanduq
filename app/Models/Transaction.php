<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    /**
     * user_id غير مذكور عمدًا — يُضبط دائمًا من المستخدم المسجّل، لا من النموذج.
     */
    protected $fillable = [
        'category_id',
        'type',
        'amount',
        'date',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // يحوّل العمود إلى كائن Carbon، فتعمل $transaction->date->format(…)
            // و ->translatedFormat(…) في ملفات Blade مباشرة.
            'date' => 'date',

            // يحافظ على منزلتين عشريتين دائمًا عند القراءة.
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
