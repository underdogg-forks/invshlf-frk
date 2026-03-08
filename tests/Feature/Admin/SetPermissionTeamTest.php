<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SetPermissionTeamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

        $user = User::find(1);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_sets_the_permission_team_id_from_a_valid_company_header(): void
    {
        /* Arrange */
        $user = User::find(1);
        $companyId = $user->companies()->first()->id;

        /* Act */
        $response = $this->withHeaders(['company' => (string) $companyId])
            ->getJson('/api/v1/bootstrap');

        /* Assert */
        $response->assertOk();
        $this->assertEquals(
            $companyId,
            app(PermissionRegistrar::class)->getPermissionsTeamId()
        );
    }

    #[Test]
    public function it_ignores_a_non_numeric_company_header_and_falls_back_to_null(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->withHeaders(['company' => 'not-a-number'])
            ->getJson('/api/v1/bootstrap');

        /* Assert */
        $response->assertOk();
        $this->assertNull(
            app(PermissionRegistrar::class)->getPermissionsTeamId()
        );
    }

    #[Test]
    public function it_ignores_a_zero_company_header_and_falls_back_to_the_users_first_company(): void
    {
        /* Arrange */
        $user = User::find(1);
        $expectedCompanyId = $user->companies()->first()->id;

        /* Act */
        $response = $this->withHeaders(['company' => '0'])
            ->getJson('/api/v1/bootstrap');

        /* Assert */
        $response->assertOk();
        $this->assertEquals(
            $expectedCompanyId,
            app(PermissionRegistrar::class)->getPermissionsTeamId()
        );
    }
}
