<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
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

        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer, ['*'], 'customer');
    }

    #[Test]
    public function it_retrieves_the_customer_dashboard(): void
    {
        // Arrange
        $customer = Auth::guard('customer')->user();

        // Act
        $response = $this->getJson("api/v1/{$customer->company->slug}/customer/dashboard");

        // Assert
        $response->assertOk();
    }
}
