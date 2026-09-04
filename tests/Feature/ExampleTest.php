<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * الجذر ليس صفحة بذاته: يحوّل إلى لوحة التحكم، وهي بدورها محمية
     * بـ middleware 'auth' فتعيد الزائر إلى تسجيل الدخول (المتطلب 3.1.4).
     */
    public function test_root_redirects_to_the_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    /**
     * زائر بلا جلسة لا يصل إلى لوحة التحكم.
     */
    public function test_guest_cannot_reach_the_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
