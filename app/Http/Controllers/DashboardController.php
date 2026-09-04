<?php

namespace App\Http\Controllers;

use App\Support\Period;
use App\Support\TransactionStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * لوحة التحكم — البند 3.3.1.
 *
 * كل الأرقام المعروضة تُحسب من جدول transactions عند كل طلب، ولا يُخزَّن رصيد
 * جاهز في أي مكان (البند 6.1 من الوثيقة). الفترة يختارها المستخدم وتصل عبر
 * الـ query string، فتنعكس على الإجماليات والرسم وأحدث الحركات معًا.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly TransactionStats $stats)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $period = Period::fromRequest($request, $user);

        $totals = $this->stats->totals($user, $period);

        return view('dashboard.index', [
            'period' => $period,

            'income' => $totals['income'],
            'expense' => $totals['expense'],
            'incomeCount' => $totals['incomeCount'],
            'expenseCount' => $totals['expenseCount'],

            'series' => $this->stats->series($user, $period),

            // أعلى خمسة تصنيفات صرفًا داخل الفترة
            'topCategories' => $this->stats->byCategory($user, $period, 'expense', limit: 5),

            'recent' => $this->stats->recent($user, $period),

            // قد تكون null إذا لم يكن للفترة السابقة رصيد يُقارَن به
            'netChange' => $this->stats->netChangePercent($user, $period),
        ]);
    }
}
