<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer, ['*'], 'customer');
    }

    #[Test]
    public function it_retrieves_all_expenses_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();

        /* Act */
        $response = $this->getJson("api/v1/{$customer->company->slug}/customer/expenses?page=1");

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_retrieves_a_single_expense_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $expense = Expense::factory()->create([
            'customer_id' => $customer->id,
            'company_id' => $customer->company->id,
        ]);

        /* Act */
        $response = $this->getJson("/api/v1/{$customer->company->slug}/customer/expenses/{$expense->id}");

        /* Assert */
        $response->assertOk();
    }
}
