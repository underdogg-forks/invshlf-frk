<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTest extends TestCase
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
    public function it_retrieves_all_payments_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();

        /* Act */
        $response = $this->getJson("api/v1/{$customer->company->slug}/customer/payments?page=1");

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_retrieves_a_single_payment_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $payment = Payment::factory()->create(['customer_id' => $customer->id]);

        /* Act */
        $response = $this->getJson("/api/v1/{$customer->company->slug}/customer/payments/{$payment->id}");

        /* Assert */
        $response->assertOk();
    }
}
