<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * صفحة الحساب: تعديل البيانات وحذف الحساب.
 *
 * خارج نطاق وثيقة SRS — أُضيفت بطلب صاحب المشروع.
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    /**
     * حفظ الاسم والبريد.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()
            ->route('profile.edit')
            ->with('status', __('app.saved_profile'));
    }

    /**
     * حذف الحساب وكل بياناته نهائيًا.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // إجراء لا رجعة فيه، فنطلب كلمة المرور حتى لا يكفي الوصول إلى جهاز
        // مفتوح لمحو كل السجلات. قاعدة current_password تقارنها بالمخزّنة.
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            // الترتيب مقصود: المفتاح الخارجي transactions.category_id معرّف
            // بـ restrictOnDelete، فحذف التصنيفات قبل الحركات ترفضه قاعدة
            // البيانات. نحذف الحركات أولًا ثم التصنيفات ثم المستخدم.
            $user->transactions()->delete();
            $user->categories()->delete();
            $user->delete();
        });

        // بعد نجاح الحذف لا قبله: لو تراجعت المعاملة لأي سبب يبقى المستخدم
        // مسجّلًا وحسابه سليمًا، بدل أن يخرج من جلسته وحسابه لم يُحذف.
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('app.account_deleted'));
    }
}
