<?php

namespace App\Http\Controllers;

use App\Support\Period;
use App\Support\TransactionStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * التقارير — البنود 3.3.2 و 3.3.3 و 3.3.4.
 *
 * نفس محرّك الحساب المستخدم في لوحة التحكم، مع إضافة فلتر التصنيف.
 */
class ReportController extends Controller
{
    public function __construct(private readonly TransactionStats $stats)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $period = Period::fromRequest($request, $user);

        $categories = $user->categories()
            ->orderBy('type')
            ->orderBy(app()->getLocale() === 'en' ? 'name_en' : 'name_ar')
            ->get();

        // نبحث عن التصنيف داخل تصنيفات هذا المستخدم لا في الجدول كله: أي معرّف
        // لا يخصّه يُهمَل بصمت، فلا يستطيع أحد استكشاف تصنيفات غيره عبر الرابط.
        $category = $categories->firstWhere('id', (int) $request->input('category_id'));
        $categoryId = $category?->id;

        $totals = $this->stats->totals($user, $period, $categoryId);

        return view('reports.index', [
            'period' => $period,
            'categories' => $categories,
            'selectedCategory' => $category,

            'income' => $totals['income'],
            'expense' => $totals['expense'],

            'series' => $this->stats->series($user, $period, $categoryId),

            // عند اختيار تصنيف نعرض توزيع نوعه هو، وإلا فالمصروفات —
            // وهي ما يهمّ المستخدم عادةً. هذا يمنع ظهور بطاقة فارغة عند
            // اختيار تصنيف دخل.
            'byCategory' => $this->stats->byCategory(
                $user,
                $period,
                $category?->type ?? 'expense',
                $categoryId,
            ),
        ]);
    }
}
