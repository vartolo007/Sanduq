<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول العمليات المالية — البند 5.3 في وثيقة SRS.
 *
 * هذا الجدول هو المصدر الوحيد للأرقام في التطبيق: كل الإجماليات في لوحة
 * التحكم والتقارير تُحسب منه عند كل طلب، ولا نخزّن رصيدًا جاهزًا في أي مكان
 * (البند 6.1 من الوثيقة).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete: لا يمكن حذف تصنيف ما زالت تعتمد عليه حركات.
            // نمنع ذلك على مستوى قاعدة البيانات حتى لا تفقد حركة تصنيفها بالخطأ.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            // النوع مكرّر هنا رغم وجوده في التصنيف، تمامًا كما في الوثيقة.
            // الفائدة: فلترة الدخل عن المصروف وحساب الإجماليات دون الحاجة
            // إلى ضمّ جدول التصنيفات في كل استعلام.
            $table->enum('type', ['income', 'expense']);

            // decimal وليس float: الأرقام العشرية في float تفقد الدقة عند الجمع،
            // وهذا غير مقبول في تطبيق مالي.
            $table->decimal('amount', 10, 2);

            $table->date('date');
            $table->string('note')->nullable();

            $table->timestamps();

            // الفهرس المركّب المطلوب في البند 5.5.
            // ترتيب الأعمدة مقصود: كل استعلاماتنا تبدأ بتحديد المستخدم ثم تفلتر
            // أو ترتّب حسب التاريخ، وهذا ما يستفيد منه الفهرس.
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
