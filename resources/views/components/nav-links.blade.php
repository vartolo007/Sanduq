@php
    // One source of truth for both the desktop sidebar and the mobile tab bar.
    $items = [
        ['route' => 'dashboard',    'label' => __('app.nav_home'),       'icon' => 'home'],
        ['route' => 'transactions.index', 'label' => __('app.nav_transactions'), 'icon' => 'receipt'],
        ['route' => 'reports.index','label' => __('app.nav_reports'),    'icon' => 'chart'],
        ['route' => 'categories.index', 'label' => __('app.nav_categories'), 'icon' => 'tag'],
        ['route' => 'profile.edit', 'label' => __('app.nav_profile'),    'icon' => 'user'],
    ];
@endphp
