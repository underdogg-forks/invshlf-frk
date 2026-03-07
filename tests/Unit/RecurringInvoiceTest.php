<?php

namespace Tests\Unit;

use App\Models\RecurringInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecurringInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_invoices(): void
    {
        // Arrange
        $recurringInvoice = RecurringInvoice::factory()->hasInvoices(5)->create();

        // Act & Assert
        $this->assertCount(5, $recurringInvoice->invoices);
        $this->assertTrue($recurringInvoice->invoices()->exists());
    }

    #[Test]
    public function it_has_many_invoice_items(): void
    {
        // Arrange
        $recurringInvoice = RecurringInvoice::factory()->hasItems(5)->create();

        // Act & Assert
        $this->assertCount(5, $recurringInvoice->items);
        $this->assertTrue($recurringInvoice->items()->exists());
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        // Arrange
        $recurringInvoice = RecurringInvoice::factory()->hasTaxes(5)->create();

        // Act & Assert
        $this->assertCount(5, $recurringInvoice->taxes);
        $this->assertTrue($recurringInvoice->taxes()->exists());
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        // Arrange
        $recurringInvoice = RecurringInvoice::factory()->forCustomer()->create();

        // Act & Assert
        $this->assertTrue($recurringInvoice->customer()->exists());
    }
}
