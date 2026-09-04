<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // القالب الافتراضي للترقيم في Laravel مكتوب بـ Tailwind، وهو غير محمّل
        // في هذا المشروع، فنستبدله بقالبنا المبني على أصناف sanduq.css.
        Paginator::defaultView('vendor.pagination.sanduq');
    }
}
