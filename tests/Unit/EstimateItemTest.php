<?php

namespace Tests\Unit;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstimateItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_an_estimate(): void
    {
        // Arrange
        $estimateItem = EstimateItem::factory()->forEstimate()->create();

        // Act & Assert
        $this->assertTrue($estimateItem->estimate()->exists());
    }

    #[Test]
    public function it_belongs_to_an_item(): void
    {
        // Arrange
        $estimateItem = EstimateItem::factory()->create([
            'item_id' => Item::factory(),
            'estimate_id' => Estimate::factory(),
        ]);

        // Act & Assert
        $this->assertTrue($estimateItem->item()->exists());
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        // Arrange
        $estimateItem = EstimateItem::factory()->hasTaxes(5)->create([
            'estimate_id' => Estimate::factory(),
        ]);

        // Act & Assert
        $this->assertCount(5, $estimateItem->taxes);
        $this->assertTrue($estimateItem->taxes()->exists());
    }
}
