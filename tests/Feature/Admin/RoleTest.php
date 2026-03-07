<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
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
    public function it_creates_a_user_with_a_super_admin_role(): void
    {
        /* Arrange */
        $data = [
            'email' => 'loremipsum@gmail.com',
            'name' => 'lorem',
            'password' => 'lorem@123',
            'companies' => [['role' => 'super admin', 'id' => 1]],
        ];

        /* Act */
        $this->postJson('api/v1/users', $data)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('users', collect($data)->only(['email', 'name'])->toArray());
    }
}
