<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    /**
     * يتيح استدعاء $this->authorize(…) في كل الكنترولرات، وهو ما نستخدمه
     * للتأكد من أن المستخدم يملك السجل قبل تعديله أو حذفه (البند 4.3).
     */
    use AuthorizesRequests;
}
