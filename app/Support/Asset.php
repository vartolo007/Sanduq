<?php

namespace App\Support;

/**
 * روابط ملفات CSS و JS مع بصمة تتغيّر مع الملف.
 *
 * بدونها يحتفظ المتصفح — وخصوصًا متصفح الجوال — بالنسخة القديمة بعد كل تعديل،
 * فيرى المستخدم تصميمًا قديمًا أو سلوكًا مختلفًا عن الكود الفعلي. إضافة زمن آخر
 * تعديل إلى الرابط تجعل كل نسخة رابطًا جديدًا فيُعاد تنزيلها مرة واحدة.
 */
class Asset
{
    public static function versioned(string $path): string
    {
        $full = public_path($path);

        $version = is_file($full) ? filemtime($full) : null;

        return asset($path).($version ? '?v='.$version : '');
    }
}
