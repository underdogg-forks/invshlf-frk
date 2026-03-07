<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

        $user = User::findOrFail(1);
        $company = $user->companies()->firstOrFail();
        $this->withHeaders(['company' => $company->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_retrieves_all_countries(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/countries');

        /* Assert */
        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'code', 'name', 'phone_code']]]);
    }
}
