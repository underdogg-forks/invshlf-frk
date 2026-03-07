<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Item\UnitsController;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_units(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/units?page=1');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_creates_a_unit(): void
    {
        /* Arrange */
        $data = [
            'name' => 'unit name',
            'company_id' => User::find(1)->companies()->first()->id,
        ];

        /* Act */
        $response = $this->postJson('api/v1/units', $data);

        /* Assert */
        $response->assertStatus(201);
        $this->assertDatabaseHas('units', $data);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            UnitsController::class,
            'store',
            UnitRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_unit(): void
    {
        /* Arrange */
        $unit = Unit::factory()->create();

        /* Act */
        $response = $this->getJson("api/v1/units/{$unit->id}");

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => $unit['name']]);
    }

    #[Test]
    public function it_updates_a_unit(): void
    {
        /* Arrange */
        $unit = Unit::factory()->create();
        $updatedData = ['name' => 'new name'];

        /* Act */
        $response = $this->putJson("api/v1/units/{$unit->id}", $updatedData);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => $updatedData['name']]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            UnitsController::class,
            'update',
            UnitRequest::class
        );
    }

    #[Test]
    public function it_deletes_a_unit(): void
    {
        /* Arrange */
        $unit = Unit::factory()->create();

        /* Act */
        $response = $this->deleteJson("api/v1/units/{$unit->id}");

        /* Assert */
        $response->assertOk();
        $this->assertModelMissing($unit);
    }
}
