<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * إنشاء حساب جديد — المتطلب 3.1.1.
 */
class RegisteredUserController extends Controller
{
    /**
     * عرض نموذج إنشاء الحساب.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * حفظ المستخدم الجديد ثم تسجيل دخوله مباشرة.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // $request->validated() يعيد فقط الحقول التي نجحت في التحقق،
        // فلا يمكن لأحد تمرير حقول إضافية لم نطلبها.
        //
        // لاحظ أننا لا نستدعي Hash::make هنا: الخاصية 'password' => 'hashed'
        // في نموذج User تتكفّل بالتشفير تلقائيًا عند الحفظ.
        $user = User::create($request->validated());

        // يبدأ الحساب بتصنيفات جاهزة بدل صفحة فارغة تُجبر المستخدم
        // على إنشاء تصنيف قبل أن يسجّل أول عملية.
        Category::createDefaultsFor($user);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
