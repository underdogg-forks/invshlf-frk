<?php

namespace Tests\Unit;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_a_unit(): void
    {
        // Arrange
        $item = Item::factory()->forUnit()->create();

        // Act & Assert
        $this->assertTrue($item->unit()->exists());
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        // Arrange
        $item = Item::factory()->hasTaxes(5)->create();

        // Act & Assert
        $this->assertCount(5, $item->taxes);
        $this->assertTrue($item->taxes()->exists());
    }

    #[Test]
    public function it_has_many_invoice_items(): void
    {
        // Arrange
        $item = Item::factory()->has(InvoiceItem::factory()->count(5)->state([
            'invoice_id' => Invoice::factory(),
        ]))->create();

        // Act & Assert
        $this->assertCount(5, $item->invoiceItems);
        $this->assertTrue($item->invoiceItems()->exists());
    }

    #[Test]
    public function it_has_many_estimate_items(): void
    {
        // Arrange
        $item = Item::factory()->has(EstimateItem::factory()
            ->count(5)
            ->state(['estimate_id' => Estimate::factory()])
        )->create();

        // Act & Assert
        $this->assertCount(5, $item->estimateItems);
        $this->assertTrue($item->estimateItems()->exists());
    }
}
