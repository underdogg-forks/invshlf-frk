<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Item\ItemsController;
use App\Http\Requests\ItemsRequest;
use App\Models\Item;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
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

        $user = User::find(1);
        $this->withHeaders(['company' => $user->companies()->first()->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_retrieves_a_paginated_list_of_items(): void
    {
        // Arrange - data seeded in setUp

        // Act
        $response = $this->getJson('api/v1/items?page=1');

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_creates_an_item_with_taxes(): void
    {
        // Arrange
        $item = Item::factory()->raw([
            'taxes' => [
                Tax::factory()->raw(),
                Tax::factory()->raw(),
            ],
        ]);

        // Act
        $response = $this->postJson('api/v1/items', $item);

        // Assert
        $this->assertDatabaseHas('items', [
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'],
            'company_id' => $item['company_id'],
        ]);
        $this->assertDatabaseHas('taxes', [
            'item_id' => $response->getData()->data->id,
        ]);
        $response->assertOk();
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            ItemsController::class,
            'store',
            ItemsRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_item(): void
    {
        // Arrange
        $item = Item::factory()->create();

        // Act
        $response = $this->getJson("api/v1/items/{$item->id}");

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('items', [
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'],
            'company_id' => $item['company_id'],
        ]);
    }

    #[Test]
    public function it_updates_an_item(): void
    {
        // Arrange
        $item = Item::factory()->create();
        $updatedItem = Item::factory()->raw([
            'taxes' => [Tax::factory()->raw()],
        ]);

        // Act
        $response = $this->putJson('api/v1/items/'.$item->id, $updatedItem);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('items', [
            'name' => $updatedItem['name'],
            'description' => $updatedItem['description'],
            'price' => $updatedItem['price'],
            'company_id' => $updatedItem['company_id'],
        ]);
        $this->assertDatabaseHas('taxes', [
            'item_id' => $item->id,
        ]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            ItemsController::class,
            'update',
            ItemsRequest::class
        );
    }

    #[Test]
    public function it_deletes_multiple_items(): void
    {
        // Arrange
        $items = Item::factory()->count(5)->create();
        $data = ['ids' => $items->pluck('id')];

        // Act
        $this->postJson('/api/v1/items/delete', $data)->assertOk();

        // Assert
        foreach ($items as $item) {
            $this->assertModelMissing($item);
        }
    }

    #[Test]
    public function it_searches_items_by_filters(): void
    {
        // Arrange
        $filters = [
            'page' => 1,
            'limit' => 15,
            'search' => 'doe',
            'price' => 6,
            'unit' => 'kg',
        ];

        // Act
        $response = $this->getJson('api/v1/items?'.http_build_query($filters, '', '&'));

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_creates_an_item_with_a_fixed_amount_tax(): void
    {
        // Arrange
        $item = Item::factory()->raw([
            'taxes' => [
                Tax::factory()->raw([
                    'calculation_type' => 'fixed',
                    'fixed_amount' => 5000,
                ]),
            ],
        ]);

        // Act
        $response = $this->postJson('api/v1/items', $item);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('items', [
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'],
            'company_id' => $item['company_id'],
        ]);
        $this->assertDatabaseHas('taxes', [
            'item_id' => $response->getData()->data->id,
            'calculation_type' => 'fixed',
            'fixed_amount' => 5000,
        ]);
    }
}
