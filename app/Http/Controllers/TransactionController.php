<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * إدارة الحركات المالية — المتطلبات 3.2.1 إلى 3.2.4.
 *
 * كل الاستعلامات تبدأ من $request->user()->transactions()، فيضيف Eloquent
 * شرط user_id تلقائيًا ولا يمكن لمستخدم رؤية بيانات غيره (البند 4.3).
 */
class TransactionController extends Controller
{
    /**
     * عدد الحركات في الصفحة الواحدة — المتطلب 3.2.2 يشترط الترقيم
     * لتفادي تحميل عدد كبير جدًا دفعة واحدة.
     */
    private const PER_PAGE = 15;

    /**
     * قائمة الحركات، مرتبة تنازليًا حسب التاريخ ومقسّمة إلى صفحات.
     */
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $term = $request->string('q')->trim()->toString();

        $transactions = $request->user()
            ->transactions()
            // with('category') تجلب التصنيفات باستعلام واحد إضافي بدل استعلام
            // لكل صف، لأن كل سطر في الجدول يعرض اسم التصنيف وأيقونته.
            ->with('category')

            ->when(in_array($type, ['income', 'expense'], true),
                fn ($query) => $query->where('type', $type))

            ->when($term !== '', fn ($query) => $query->where(
                fn ($group) => $group
                    ->where('note', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($category) => $category
                        ->where('name_ar', 'like', "%{$term}%")
                        ->orWhere('name_en', 'like', "%{$term}%"))
            ))

            // الأحدث أولًا كما ينص المتطلب 3.2.2. الترتيب الثاني على id
            // يضمن ترتيبًا ثابتًا بين حركات تحمل نفس التاريخ.
            ->orderByDesc('date')
            ->orderByDesc('id')

            ->paginate(self::PER_PAGE);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * نموذج إضافة حركة جديدة.
     */
    public function create(Request $request): View
    {
        return view('transactions.create', [
            'categories' => $this->categoriesFor($request->user()),
        ]);
    }

    /**
     * حفظ حركة جديدة — المتطلب 3.2.1.
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        $request->user()->transactions()->create($request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('status', __('app.saved_tx'));
    }

    /**
     * نموذج تعديل حركة — المتطلب 3.2.3.
     */
    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => $this->categoriesFor($request->user()),
        ]);
    }

    /**
     * حفظ تعديلات حركة.
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('status', __('app.saved_tx'));
    }

    /**
     * حذف حركة — المتطلب 3.2.4.
     *
     * تأكيد الحذف يتم في الواجهة عبر نافذة sqConfirmDelete قبل إرسال النموذج.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return back()->with('status', __('app.deleted_tx'));
    }

    /**
     * تصنيفات المستخدم لقائمة الاختيار في النموذج.
     *
     * نرسلها كلها (دخل ومصروف) لأن الصفحة تخفي غير المطابق للنوع المختار
     * بجافاسكربت، ويبقى التحقق من التطابق على الخادم في TransactionRequest.
     *
     * @return Collection<int, \App\Models\Category>
     */
    private function categoriesFor(User $user): Collection
    {
        return $user->categories()
            ->orderBy('type')
            ->orderBy(app()->getLocale() === 'en' ? 'name_en' : 'name_ar')
            ->get();
    }
}
