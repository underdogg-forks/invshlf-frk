<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\CustomField\CustomFieldsController;
use App\Http\Requests\CustomFieldRequest;
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_custom_fields(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/custom-fields?page=1');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_creates_a_custom_field(): void
    {
        /* Arrange */
        $data = CustomField::factory()->raw();

        /* Act */
        $this->postJson('api/v1/custom-fields', $data)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('custom_fields', [
            'name' => $data['name'],
            'label' => $data['label'],
            'type' => $data['type'],
            'model_type' => $data['model_type'],
            'is_required' => $data['is_required'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            CustomFieldsController::class,
            'store',
            CustomFieldRequest::class
        );
    }

    #[Test]
    public function it_updates_a_custom_field(): void
    {
        /* Arrange */
        $customField = CustomField::factory()->create();
        $updatedData = CustomField::factory()->raw(['is_required' => false]);

        /* Act */
        $this->putJson('api/v1/custom-fields/'.$customField->id, $updatedData)->assertStatus(200);

        /* Assert */
        $this->assertDatabaseHas('custom_fields', [
            'id' => $customField->id,
            'name' => $updatedData['name'],
            'label' => $updatedData['label'],
            'type' => $updatedData['type'],
            'model_type' => $updatedData['model_type'],
            'is_required' => $updatedData['is_required'],
        ]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            CustomFieldsController::class,
            'update',
            CustomFieldRequest::class
        );
    }

    #[Test]
    public function it_deletes_a_custom_field(): void
    {
        /* Arrange */
        $customField = CustomField::factory()->create();

        /* Act */
        $response = $this->deleteJson('api/v1/custom-fields/'.$customField->id);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertModelMissing($customField);
    }
}
