<?php

namespace Tests\Unit;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
    }

    #[Test]
    public function it_belongs_to_an_invoice(): void
    {
        // Arrange
        $payment = Payment::factory()->forInvoice()->create();

        // Act & Assert
        $this->assertTrue($payment->invoice()->exists());
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        // Arrange
        $payment = Payment::factory()->forCustomer()->create();

        // Act & Assert
        $this->assertTrue($payment->customer()->exists());
    }

    #[Test]
    public function it_belongs_to_a_payment_method(): void
    {
        // Arrange
        $payment = Payment::factory()->forPaymentMethod()->create();

        // Act & Assert
        $this->assertTrue($payment->paymentMethod()->exists());
    }
}
