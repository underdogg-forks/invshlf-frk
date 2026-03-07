<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
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
    public function it_retrieves_dashboard_data(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/dashboard');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_searches_by_name(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/search?name=ab');

        /* Assert */
        $response->assertOk();
    }
}
