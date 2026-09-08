<?php
namespace Tests\Feature;
use App\Mail\PasswordOtpMail;
use App\Models\User;
use App\Support\PasswordOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordOtpTest extends TestCase {
  use RefreshDatabase;

  protected function setUp(): void { parent::setUp(); RateLimiter::clear('pw-otp-send:a@b.com'); RateLimiter::clear('pw-otp-verify:a@b.com'); }

  private function user(): User { return User::factory()->create(['email'=>'a@b.com']); }

  public function test_code_is_emailed_and_stored_hashed(): void {
    Mail::fake();
    $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com'])->assertRedirect(route('password.reset'));
    Mail::assertSent(PasswordOtpMail::class, function ($m) {
      $this->assertMatchesRegularExpression('/^\d{6}$/', $m->code);
      $row = DB::table('password_reset_tokens')->where('email','a@b.com')->first();
      $this->assertNotSame($m->code, $row->token, 'code must NOT be stored in plain text');
      $this->assertTrue(Hash::check($m->code, $row->token));
      return true;
    });
  }

  public function test_full_reset_flow_works(): void {
    Mail::fake(); $user = $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com']);
    $code = null; Mail::assertSent(PasswordOtpMail::class, function($m) use (&$code){ $code=$m->code; return true; });
    $this->post(route('password.store'), ['email'=>'a@b.com','code'=>$code,'password'=>'newpass1234','password_confirmation'=>'newpass1234'])
         ->assertRedirect(route('login'));
    $this->assertTrue(Hash::check('newpass1234', $user->fresh()->password));
    $this->assertDatabaseMissing('password_reset_tokens', ['email'=>'a@b.com']);
  }

  public function test_code_is_single_use(): void {
    Mail::fake(); $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com']);
    $code=null; Mail::assertSent(PasswordOtpMail::class, function($m) use (&$code){ $code=$m->code; return true; });
    $this->post(route('password.store'), ['email'=>'a@b.com','code'=>$code,'password'=>'newpass1234','password_confirmation'=>'newpass1234']);
    $this->post(route('password.store'), ['email'=>'a@b.com','code'=>$code,'password'=>'other12345','password_confirmation'=>'other12345'])
         ->assertSessionHasErrors('code');
  }

  public function test_wrong_code_is_rejected_and_locks_after_5_attempts(): void {
    Mail::fake(); $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com']);
    for ($i=0; $i<5; $i++) {
      $this->post(route('password.store'), ['email'=>'a@b.com','code'=>'000000','password'=>'newpass1234','password_confirmation'=>'newpass1234'])
           ->assertSessionHasErrors('code');
    }
    $this->assertTrue(PasswordOtp::tooManyAttempts('a@b.com'), 'must lock out after 5 wrong attempts');
  }

  public function test_expired_code_is_rejected(): void {
    Mail::fake(); $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com']);
    $code=null; Mail::assertSent(PasswordOtpMail::class, function($m) use (&$code){ $code=$m->code; return true; });
    DB::table('password_reset_tokens')->where('email','a@b.com')->update(['created_at'=>now()->subMinutes(PasswordOtp::EXPIRY_MINUTES + 1)]);
    $this->post(route('password.store'), ['email'=>'a@b.com','code'=>$code,'password'=>'newpass1234','password_confirmation'=>'newpass1234'])
         ->assertSessionHasErrors('code');
  }

  /**
   * زر "إرسال رمز جديد" في صفحة إدخال الرمز يجب أن يرسل رمزًا فعلًا.
   *
   * كان رابطًا إلى password.request — أي GET على صفحة إدخال البريد — فيعيد
   * المستخدم خطوة إلى الوراء دون إرسال شيء، رغم أن نصّه يعده بذلك.
   */
  public function test_resend_from_reset_page_sends_a_new_code(): void {
    Mail::fake(); $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com']);
    $first=null; Mail::assertSent(PasswordOtpMail::class, function($m) use (&$first){ $first=$m->code; return true; });

    // نتحقق من النموذج لا من الرابط: password.email و password.request يتشاركان
    // المسار /forgot-password ويختلفان في الطريقة فقط، فمقارنة الرابط وحده
    // كانت ستمرّ على النسخة المعطوبة أيضًا.
    $page = $this->get(route('password.reset'))->assertOk();
    $page->assertSee('<form method="POST" action="'.route('password.email').'"', false);
    $page->assertDontSee('<a href="'.route('password.request').'"', false);

    // حاجز الدقيقة يمنع الإرسال الفوري؛ نتخطّاه لنختبر الإرسال نفسه.
    RateLimiter::clear('pw-otp-send:a@b.com');

    $this->post(route('password.email'), ['email'=>'a@b.com'])
         ->assertRedirect(route('password.reset'))->assertSessionHas('status');

    $codes=[]; Mail::assertSent(PasswordOtpMail::class, function($m) use (&$codes){ $codes[]=$m->code; return true; });
    $this->assertCount(2, $codes, 'resend must send a second code');
    $this->assertNotSame($first, $codes[1], 'the resent code must be a new one');
  }

  public function test_unknown_email_sends_nothing_but_looks_identical(): void {
    Mail::fake();
    $this->post(route('password.email'), ['email'=>'nobody@b.com'])
         ->assertRedirect(route('password.reset'))->assertSessionHas('status');
    Mail::assertNothingSent();
  }

  public function test_resend_is_throttled(): void {
    Mail::fake(); $this->user();
    $this->post(route('password.email'), ['email'=>'a@b.com']);
    $this->post(route('password.email'), ['email'=>'a@b.com'])->assertSessionHasErrors('email');
    Mail::assertSentCount(1);
  }
}
