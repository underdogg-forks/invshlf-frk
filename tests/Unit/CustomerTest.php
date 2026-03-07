<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_estimates(): void
    {
        // Arrange
        $customer = Customer::factory()->hasEstimates(5)->create();

        // Act & Assert
        $this->assertCount(5, $customer->estimates);
        $this->assertTrue($customer->estimates()->exists());
    }

    #[Test]
    public function it_has_many_expenses(): void
    {
        // Arrange
        $customer = Customer::factory()->hasExpenses(5)->create();

        // Act & Assert
        $this->assertCount(5, $customer->expenses);
        $this->assertTrue($customer->expenses()->exists());
    }

    #[Test]
    public function it_has_many_invoices(): void
    {
        // Arrange
        $customer = Customer::factory()->hasInvoices(5)->create();

        // Act & Assert
        $this->assertCount(5, $customer->invoices);
        $this->assertTrue($customer->invoices()->exists());
    }

    #[Test]
    public function it_has_many_payments(): void
    {
        // Arrange
        $customer = Customer::factory()->hasPayments(5)->create();

        // Act & Assert
        $this->assertCount(5, $customer->payments);
        $this->assertTrue($customer->payments()->exists());
    }

    #[Test]
    public function it_has_many_addresses(): void
    {
        // Arrange
        $customer = Customer::factory()->hasAddresses(5)->create();

        // Act & Assert
        $this->assertCount(5, $customer->addresses);
        $this->assertTrue($customer->addresses()->exists());
    }

    #[Test]
    public function it_belongs_to_a_currency(): void
    {
        // Arrange
        $customer = Customer::factory()->create();

        // Act & Assert
        $this->assertTrue($customer->currency()->exists());
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        // Arrange
        $customer = Customer::factory()->forCompany()->create();

        // Act & Assert
        $this->assertTrue($customer->company()->exists());
    }

    #[Test]
    public function it_has_one_billing_address(): void
    {
        // Arrange
        $customer = Customer::factory()->has(Address::factory()->state([
            'type' => Address::BILLING_TYPE,
        ]))->create();

        // Act & Assert
        $this->assertTrue($customer->billingAddress()->exists());
    }

    #[Test]
    public function it_has_one_shipping_address(): void
    {
        // Arrange
        $customer = Customer::factory()->has(Address::factory()->state([
            'type' => Address::SHIPPING_TYPE,
        ]))->create();

        // Act & Assert
        $this->assertTrue($customer->shippingAddress()->exists());
    }
}
