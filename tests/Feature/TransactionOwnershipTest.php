<?php
namespace Tests\Feature;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * صلاحيات تعديل وحذف الحركات — البند 4.3.
 *
 * الاختبار الأخير هنا هو الأهم: كان التطبيق يعمل محليًا ويرفض كل تعديل بـ 403
 * بعد الرفع على السيرفر، لأن السياسة كانت تقارن user_id بـ === وبعض إعدادات
 * PDO/MySQL تعيد المفاتيح الأجنبية نصًا "3" بدل الرقم 3. الاختبارات تعمل على
 * SQLite التي تعيد أرقامًا دائمًا، فلا تكشف الفرق ما لم نحاكه صراحةً.
 */
class TransactionOwnershipTest extends TestCase {
  use RefreshDatabase;

  private function categoryFor(User $user): Category {
    return $user->categories()->create(['name_ar'=>'راتب','name_en'=>'Salary','type'=>'income','icon'=>'briefcase']);
  }

  private function txFor(User $user): Transaction {
    $category = $this->categoryFor($user);

    return $user->transactions()->create([
      'category_id' => $category->id,
      'type' => 'income',
      'amount' => 100,
      'date' => '2026-01-01',
    ]);
  }

  public function test_owner_can_edit_and_delete_own_transaction(): void {
    $user = User::factory()->create();
    $tx = $this->txFor($user);

    $this->actingAs($user)->get(route('transactions.edit', $tx))->assertOk();

    $this->actingAs($user)->put(route('transactions.update', $tx), [
      'type'=>'income', 'category_id'=>$tx->category_id, 'amount'=>250, 'date'=>'2026-01-02',
    ])->assertRedirect(route('transactions.index'));
    $this->assertSame('250.00', $tx->fresh()->amount);

    $this->actingAs($user)->delete(route('transactions.destroy', $tx));
    $this->assertDatabaseMissing('transactions', ['id'=>$tx->id]);
  }

  public function test_stranger_cannot_touch_another_users_transaction(): void {
    $tx = $this->txFor(User::factory()->create());
    $stranger = User::factory()->create();

    // تصنيف يملكه الغريب، حتى يمرّ التحقق ويصل الطلب إلى السياسة —
    // فنختبر الرفض بسبب الملكية لا بسبب قاعدة تحقق سابقة له.
    $ownCategory = $this->categoryFor($stranger);

    $this->actingAs($stranger)->get(route('transactions.edit', $tx))->assertForbidden();
    $this->actingAs($stranger)->put(route('transactions.update', $tx), [
      'type'=>'income', 'category_id'=>$ownCategory->id, 'amount'=>250, 'date'=>'2026-01-02',
    ])->assertForbidden();
    $this->actingAs($stranger)->delete(route('transactions.destroy', $tx))->assertForbidden();
    $this->assertDatabaseHas('transactions', ['id'=>$tx->id]);
  }

  /**
   * محاكاة السيرفر: نحقن user_id نصًا كما تعيده بعض إعدادات PDO، ونتأكد أن
   * المالك ما زال مسموحًا له. بدون تثبيت النوع كانت هذه الحالة تعطي 403.
   */
  public function test_ownership_holds_when_foreign_key_arrives_as_string(): void {
    $user = User::factory()->create();
    $tx = $this->txFor($user);

    // setRawAttributes يتخطّى الـ mutators ويضع القيمة كما تصل من قاعدة البيانات.
    $tx->setRawAttributes(array_merge($tx->getAttributes(), ['user_id' => (string) $user->id]), true);

    $this->assertTrue(Gate::forUser($user)->allows('update', $tx));
    $this->assertTrue(Gate::forUser($user)->allows('delete', $tx));
    $this->assertFalse(Gate::forUser(User::factory()->create())->allows('update', $tx));
  }
}
