<?php

namespace Tests\Unit;

use App\Http\Requests\EstimatesRequest;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstimateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_estimate_items(): void
    {
        // Arrange
        $estimate = Estimate::factory()->hasItems(5)->create();

        // Act
        $itemCount = $estimate->items()->count();

        // Assert
        $this->assertCount(5, $estimate->items);
        $this->assertEquals(5, $itemCount);
        $this->assertTrue($estimate->items()->exists());
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        // Arrange
        $estimate = Estimate::factory()->forCustomer()->create();

        // Act & Assert
        $this->assertTrue($estimate->customer()->exists());
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        // Arrange
        $estimate = Estimate::factory()->hasTaxes(5)->create();

        // Act
        $taxCount = $estimate->taxes()->count();

        // Assert
        $this->assertCount(5, $estimate->taxes);
        $this->assertEquals(5, $taxCount);
        $this->assertTrue($estimate->taxes()->exists());
    }

    #[Test]
    public function it_creates_an_estimate_with_items_and_taxes(): void
    {
        // Arrange
        $estimateData = Estimate::factory()->raw();
        $item = EstimateItem::factory()->raw();

        $estimateData['items'] = [$item];
        $estimateData['taxes'] = [Tax::factory()->raw()];

        $request = new EstimatesRequest;
        $request->replace($estimateData);

        // Act
        $response = Estimate::createEstimate($request);

        // Assert
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $response->id,
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'total' => $item['total'],
        ]);
        $this->assertDatabaseHas('estimates', [
            'estimate_number' => $estimateData['estimate_number'],
            'customer_id' => $estimateData['customer_id'],
            'template_name' => $estimateData['template_name'],
            'sub_total' => $estimateData['sub_total'],
            'total' => $estimateData['total'],
            'discount' => $estimateData['discount'],
            'discount_type' => $estimateData['discount_type'],
            'discount_val' => $estimateData['discount_val'],
            'tax' => $estimateData['tax'],
            'notes' => $estimateData['notes'],
        ]);
    }

    #[Test]
    public function it_updates_an_estimate_with_new_items_and_taxes(): void
    {
        // Arrange
        $estimate = Estimate::factory()->hasItems()->hasTaxes()->create();
        $newEstimateData = Estimate::factory()->raw();
        $item = EstimateItem::factory()->raw(['estimate_id' => $estimate->id]);

        $newEstimateData['items'] = [$item];
        $newEstimateData['taxes'] = [Tax::factory()->raw()];

        $request = new EstimatesRequest;
        $request->replace($newEstimateData);

        // Act
        $estimate->updateEstimate($request);

        // Assert
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $estimate->id,
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'],
            'total' => $item['total'],
            'quantity' => $item['quantity'],
        ]);
        $this->assertDatabaseHas('estimates', [
            'estimate_number' => $newEstimateData['estimate_number'],
            'customer_id' => $newEstimateData['customer_id'],
            'template_name' => $newEstimateData['template_name'],
            'sub_total' => $newEstimateData['sub_total'],
            'total' => $newEstimateData['total'],
            'discount' => $newEstimateData['discount'],
            'discount_type' => $newEstimateData['discount_type'],
            'discount_val' => $newEstimateData['discount_val'],
            'tax' => $newEstimateData['tax'],
            'notes' => $newEstimateData['notes'],
        ]);
    }

    #[Test]
    public function it_creates_items_for_an_estimate(): void
    {
        // Arrange
        $estimate = Estimate::factory()->create();
        $item = EstimateItem::factory()->raw(['invoice_id' => $estimate->id]);
        $request = new Request;
        $request->replace(['items' => [$item]]);

        // Act
        Estimate::createItems($estimate, $request, $estimate->exchange_rate);

        // Assert
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $estimate->id,
            'description' => $item['description'],
            'price' => $item['price'],
            'tax' => $item['tax'],
            'quantity' => $item['quantity'],
            'total' => $item['total'],
        ]);
        $this->assertCount(1, $estimate->items);
    }

    #[Test]
    public function it_creates_taxes_for_an_estimate(): void
    {
        // Arrange
        $estimate = Estimate::factory()->create();
        $taxes = [
            Tax::factory()->raw(['estimate_id' => $estimate->id]),
            Tax::factory()->raw(['estimate_id' => $estimate->id]),
        ];
        $request = new Request;
        $request->replace(['taxes' => $taxes]);

        // Act
        Estimate::createTaxes($estimate, $request, $estimate->exchange_rate);

        // Assert
        $this->assertCount(2, $estimate->taxes);
        $this->assertDatabaseHas('taxes', [
            'estimate_id' => $estimate->id,
            'name' => $taxes[0]['name'],
            'amount' => $taxes[0]['amount'],
        ]);
    }
}
