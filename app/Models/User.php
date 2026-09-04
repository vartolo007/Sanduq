<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * تصنيفات المستخدم — علاقة واحد إلى متعدد (البند 5.4).
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * حركات المستخدم المالية — علاقة واحد إلى متعدد (البند 5.4).
     *
     * كل استعلامات التطبيق تمرّ من هنا: $user->transactions()->… تضيف
     * شرط user_id تلقائيًا، فيستحيل أن يرى مستخدم بيانات غيره (البند 4.3).
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * الأحرف الأولى من اسم المستخدم، تُعرض في الأفاتار بالهيدر وصفحة الحساب.
     *
     * "عبدالرحمن الأزهري" ‏→‏ "ع ا"
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $part) => Str::substr($part, 0, 1))
            ->implode(' ');
    }
}
