<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * الخطوة الأولى من استعادة كلمة المرور: إرسال رابط إعادة التعيين.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * عرض نموذج "نسيت كلمة المرور".
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * توليد رمز إعادة تعيين وإرساله على بريد المستخدم.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // sendResetLink يتكفّل بكل شيء: يبحث عن المستخدم، يولّد رمزًا عشوائيًا،
        // يخزّنه مشفّرًا في جدول password_reset_tokens، ثم يرسل بريد الاستعادة.
        $status = Password::sendResetLink($request->only('email'));

        // نعرض نفس الرسالة سواء وُجد البريد أم لا، حتى لا يستطيع أحد استخدام
        // هذه الصفحة لمعرفة البُرد الإلكترونية المسجّلة في النظام.
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
