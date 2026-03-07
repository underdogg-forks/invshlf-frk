<?php

namespace Tests\Unit;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_a_tax_type(): void
    {
        /* Arrange */
        $tax = Tax::factory()->create();

        /* Act & Assert */
        $this->assertTrue($tax->taxType()->exists());
    }

    #[Test]
    public function it_belongs_to_an_invoice(): void
    {
        /* Arrange */
        $tax = Tax::factory()->forInvoice()->create();

        /* Act & Assert */
        $this->assertTrue($tax->invoice()->exists());
    }

    #[Test]
    public function it_belongs_to_a_recurring_invoice(): void
    {
        /* Arrange */
        $tax = Tax::factory()->forRecurringInvoice()->create();

        /* Act & Assert */
        $this->assertTrue($tax->recurringInvoice()->exists());
    }

    #[Test]
    public function it_belongs_to_an_estimate(): void
    {
        /* Arrange */
        $tax = Tax::factory()->forEstimate()->create();

        /* Act & Assert */
        $this->assertTrue($tax->estimate()->exists());
    }

    #[Test]
    public function it_belongs_to_an_invoice_item(): void
    {
        /* Arrange */
        $tax = Tax::factory()->for(InvoiceItem::factory()->state([
            'invoice_id' => Invoice::factory(),
        ]))->create();

        /* Act & Assert */
        $this->assertTrue($tax->invoiceItem()->exists());
    }

    #[Test]
    public function it_belongs_to_an_estimate_item(): void
    {
        /* Arrange */
        $tax = Tax::factory()->for(EstimateItem::factory()->state([
            'estimate_id' => Estimate::factory(),
        ]))->create();

        /* Act & Assert */
        $this->assertTrue($tax->estimateItem()->exists());
    }

    #[Test]
    public function it_belongs_to_an_item(): void
    {
        /* Arrange */
        $tax = Tax::factory()->forItem()->create();

        /* Act & Assert */
        $this->assertTrue($tax->item()->exists());
    }
}
