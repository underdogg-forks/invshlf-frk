<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Users\UsersController;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

        $user = User::where('role', 'super admin')->first();
        $this->withHeaders(['company' => $user->companies()->first()->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_retrieves_all_users(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('/api/v1/users');

        /* Assert */
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            UsersController::class,
            'store',
            UserRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_user(): void
    {
        /* Arrange */
        $user = User::factory()->create();

        /* Act & Assert */
        $this->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            UsersController::class,
            'update',
            UserRequest::class
        );
    }

    #[Test]
    public function it_removes_stale_role_assignments_when_a_company_is_removed_from_a_user(): void
    {
        /* Arrange */
        $owner = User::find(1);
        $company = $owner->companies()->first();
        setPermissionsTeamId($company->id);

        $newUser = User::factory()->create();
        $newUser->companies()->attach($company->id);
        $newUser->assignRole('super admin');

        $payload = [
            'name' => $newUser->name,
            'email' => $newUser->email,
            'companies' => [],
        ];

        /* Act */
        $this->putJson("/api/v1/users/{$newUser->id}", $payload)->assertOk();

        /* Assert */
        $this->assertDatabaseMissing('model_has_roles', [
            'model_id' => $newUser->id,
            'team_id' => $company->id,
        ]);
    }

    #[Test]
    public function it_retains_role_assignments_for_companies_that_remain_attached(): void
    {
        /* Arrange */
        $owner = User::find(1);
        $company = $owner->companies()->first();
        setPermissionsTeamId($company->id);

        $newUser = User::factory()->create();
        $newUser->companies()->attach($company->id);
        $newUser->assignRole('super admin');

        $payload = [
            'name' => $newUser->name,
            'email' => $newUser->email,
            'companies' => [['id' => $company->id, 'role' => 'super admin']],
        ];

        /* Act */
        $this->putJson("/api/v1/users/{$newUser->id}", $payload)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $newUser->id,
            'team_id' => $company->id,
        ]);
    }
}
