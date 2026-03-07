<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
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
        $invoiceItem = InvoiceItem::factory()->forInvoice()->create();

        // Act & Assert
        $this->assertTrue($invoiceItem->invoice()->exists());
    }

    #[Test]
    public function it_belongs_to_an_item(): void
    {
        // Arrange
        $invoiceItem = InvoiceItem::factory()->create([
            'item_id' => Item::factory(),
            'invoice_id' => Invoice::factory(),
        ]);

        // Act & Assert
        $this->assertTrue($invoiceItem->item()->exists());
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        // Arrange
        $invoiceItem = InvoiceItem::factory()->hasTaxes(5)->create([
            'invoice_id' => Invoice::factory(),
        ]);

        // Act & Assert
        $this->assertCount(5, $invoiceItem->taxes);
        $this->assertTrue($invoiceItem->taxes()->exists());
    }
}
