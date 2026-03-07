<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Settings\TaxTypesController;
use App\Http\Requests\TaxTypeRequest;
use App\Models\TaxType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaxTypeTest extends TestCase
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
    public function it_retrieves_all_tax_types(): void
    {
        // Arrange - data seeded in setUp

        // Act
        $response = $this->getJson('api/v1/tax-types');

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_creates_a_tax_type(): void
    {
        // Arrange
        $taxType = TaxType::factory()->raw();

        // Act
        $this->postJson('api/v1/tax-types', $taxType);

        // Assert
        $this->assertDatabaseHas('tax_types', $taxType);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            TaxTypesController::class,
            'store',
            TaxTypeRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_tax_type(): void
    {
        // Arrange
        $taxType = TaxType::factory()->create();

        // Act
        $response = $this->getJson('api/v1/tax-types/'.$taxType->id);

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_updates_a_tax_type(): void
    {
        // Arrange
        $taxType = TaxType::factory()->create();
        $updatedData = TaxType::factory()->raw();

        // Act
        $response = $this->putJson('api/v1/tax-types/'.$taxType->id, $updatedData);

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            TaxTypesController::class,
            'update',
            TaxTypeRequest::class
        );
    }

    #[Test]
    public function it_deletes_a_tax_type(): void
    {
        // Arrange
        $taxType = TaxType::factory()->create();

        // Act
        $response = $this->deleteJson('api/v1/tax-types/'.$taxType->id);

        // Assert
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertModelMissing($taxType);
    }

    #[Test]
    public function it_creates_a_tax_type_with_a_negative_percentage(): void
    {
        // Arrange
        $taxType = TaxType::factory()->raw(['percent' => -9.99]);

        // Act
        $this->postJson('api/v1/tax-types', $taxType)->assertStatus(201);

        // Assert
        $this->assertDatabaseHas('tax_types', $taxType);
    }

    #[Test]
    public function it_creates_a_tax_type_with_a_fixed_amount(): void
    {
        // Arrange
        $taxType = TaxType::factory()->raw([
            'calculation_type' => 'fixed',
            'percent' => null,
            'fixed_amount' => 5000,
        ]);

        // Act
        $this->postJson('api/v1/tax-types', $taxType)->assertStatus(201);

        // Assert
        $this->assertDatabaseHas('tax_types', $taxType);
    }
}
