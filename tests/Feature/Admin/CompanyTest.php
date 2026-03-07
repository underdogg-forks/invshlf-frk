<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Company\CompaniesController;
use App\Http\Requests\CompaniesRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyTest extends TestCase
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
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            CompaniesController::class,
            'store',
            CompaniesRequest::class
        );
    }

    #[Test]
    public function it_creates_a_company(): void
    {
        // Arrange
        $company = Company::factory()->raw([
            'currency' => 12,
            'address' => ['country_id' => 12],
        ]);

        // Act
        $this->postJson('/api/v1/companies', $company)->assertStatus(201);

        // Assert
        $this->assertDatabaseHas('companies', collect($company)->only(['name'])->toArray());
    }

    #[Test]
    public function it_returns_a_validation_error_when_deleting_with_invalid_data(): void
    {
        // Arrange - invalid payload

        // Act
        $response = $this->postJson('/api/v1/companies/delete', ['xyz']);

        // Assert
        $response->assertStatus(422);
    }

    #[Test]
    public function it_transfers_ownership_to_another_user(): void
    {
        // Arrange
        $company = Company::factory()->create();
        $user = User::factory()->create();

        // Act & Assert
        $this->postJson('/api/v1/transfer/ownership/'.$user->id)->assertOk();
    }

    #[Test]
    public function it_retrieves_all_companies(): void
    {
        // Arrange - data seeded in setUp

        // Act
        $response = $this->getJson('/api/v1/companies');

        // Assert
        $response->assertOk();
    }
}
