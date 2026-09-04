<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول التصنيفات — البند 5.2 في وثيقة SRS.
 *
 * كل تصنيف يخص مستخدمًا واحدًا، وله نوع ثابت (دخل أو مصروف) يحدّد
 * أين يظهر في نموذج إضافة الحركة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // constrained() تنشئ المفتاح الخارجي نحو جدول users تلقائيًا.
            // cascadeOnDelete: حذف المستخدم يحذف تصنيفاته معه، فلا تبقى صفوف يتيمة.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // اسمان بدل واحد ليتبع التصنيف لغة الواجهة عند التبديل.
            // التصنيفات الافتراضية تأتي مترجمة، وما ينشئه المستخدم يُخزَّن في العمودين معًا.
            $table->string('name_ar');
            $table->string('name_en');

            $table->enum('type', ['income', 'expense']);

            // مفتاح أيقونة من الأيقونات المعرّفة في resources/views/components/icon.blade.php
            $table->string('icon', 32)->default('tag');

            $table->timestamps();

            // صفحة التصنيفات تعرضها مقسّمة حسب النوع لمستخدم واحد،
            // وهذا الفهرس يخدم ذلك الاستعلام مباشرة.
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
