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
}
