<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * إدارة التصنيفات — المتطلب 3.2.5.
 */
class CategoryController extends Controller
{
    /**
     * عرض تصنيفات المستخدم مقسّمة حسب النوع في الواجهة.
     */
    public function index(Request $request): View
    {
        $categories = $request->user()
            ->categories()
            // withCount تضيف عمودًا محسوبًا transactions_count عبر استعلام فرعي
            // واحد، بدل استدعاء العدّ لكل تصنيف على حدة (مشكلة N+1).
            ->withCount('transactions')
            ->orderBy($this->nameColumn())
            ->get();

        return view('categories.index', [
            'categories' => $categories,
            // يصل من نموذج الحركة عندما يختار المستخدم "+ تصنيف جديد"،
            // لنعيده إلى حيث كان بعد الحفظ.
            'returnTo' => $this->safeReturnPath($request->input('return')),
        ]);
    }

    /**
     * حفظ تصنيف جديد.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $category = $request->user()->categories()->create([
            // النموذج يحتوي حقلي اسم منفصلين، فيتبع التصنيف لغة الواجهة عند التبديل
            // تمامًا كالتصنيفات الافتراضية.
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'type' => $data['type'],
            // لا يختار المستخدم أيقونة في النموذج، فنعطيه أيقونة محايدة حسب النوع.
            'icon' => $data['type'] === 'income' ? 'trend' : 'tag',
        ]);

        // إن جاء المستخدم من نموذج حركة، نعيده إليه والتصنيف الجديد مختار.
        if ($returnTo = $this->safeReturnPath($request->input('return'))) {
            $separator = str_contains($returnTo, '?') ? '&' : '?';

            return redirect($returnTo.$separator.'category_id='.$category->id)
                ->with('status', __('app.saved_category'));
        }

        return redirect()
            ->route('categories.index')
            ->with('status', __('app.saved_category'));
    }

    /**
     * تعديل تصنيف قائم.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $data = $request->validated();

        // نعدّل الاسمين والنوع فقط، ونترك الأيقونة كما هي حتى لا نمحو أيقونة
        // التصنيفات الافتراضية المميّزة (النموذج لا يحوي منتقي أيقونات).
        $category->update([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'type' => $data['type'],
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', __('app.saved_category'));
    }

    /**
     * حذف تصنيف.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        // قاعدة البيانات ترفض الحذف أصلًا (restrictOnDelete)، لكننا نفحص هنا
        // لنعرض رسالة مفهومة بدل صفحة خطأ.
        if ($category->transactions()->exists()) {
            return back()->with('status', __('app.err_category_in_use'));
        }

        $category->delete();

        return back()->with('status', __('app.deleted_category'));
    }

    /**
     * يقبل مسارًا داخليًا فقط ويرفض ما عداه.
     *
     * بدون هذا الفحص يستطيع أحدهم صياغة رابط ينتهي بإعادة توجيه المستخدم إلى
     * موقع خارجي بعد الحفظ (ثغرة Open Redirect). اشتراط أن يبدأ المسار بشرطة
     * مائلة واحدة يستبعد "https://…" و "//evil.example" معًا.
     */
    private function safeReturnPath(?string $return): ?string
    {
        if ($return === null || $return === '') {
            return null;
        }

        if (! str_starts_with($return, '/') || str_starts_with($return, '//')) {
            return null;
        }

        return $return;
    }

    /**
     * عمود الاسم الموافق للغة الواجهة، يُستخدم في الترتيب.
     */
    private function nameColumn(): string
    {
        return app()->getLocale() === 'en' ? 'name_en' : 'name_ar';
    }
}
