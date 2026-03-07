<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Estimate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstimateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

        $this->customer = Customer::factory()->create();
        Sanctum::actingAs($this->customer, ['*'], 'customer');
    }

    #[Test]
    public function it_retrieves_all_estimates_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();

        /* Act */
        $response = $this->getJson("api/v1/{$customer->company->slug}/customer/estimates?page=1");

        /* Assert */
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $estimate = Estimate::factory()->create(['customer_id' => $customer->id]);

        /* Act */
        $response = $this->getJson("/api/v1/{$customer->company->slug}/customer/estimates/{$estimate->id}");

        /* Assert */
        $response->assertOk()
            ->assertJsonFragment(['id' => $estimate->id, 'estimate_number' => $estimate->estimate_number]);
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $estimate = Estimate::factory()->create([
            'estimate_date' => '1988-07-18',
            'expiry_date' => '1988-08-18',
            'customer_id' => $customer->id,
        ]);
        $status = ['status' => Estimate::STATUS_ACCEPTED];

        /* Act */
        $response = $this->postJson(
            "api/v1/{$customer->company->slug}/customer/estimate/{$estimate->id}/status",
            $status
        );

        /* Assert */
        $response->assertOk();

        /* Assert */
        $this->assertEquals(Estimate::STATUS_ACCEPTED, $response->json()['data']['status']);
    }

    #[Test]
    public function it_marks_a_customer_estimate_as_rejected(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $estimate = Estimate::factory()->create([
            'estimate_date' => '1988-07-18',
            'expiry_date' => '1988-08-18',
            'customer_id' => $customer->id,
        ]);
        $status = ['status' => Estimate::STATUS_REJECTED];

        /* Act */
        $response = $this->postJson(
            "api/v1/{$customer->company->slug}/customer/estimate/{$estimate->id}/status",
            $status
        )->assertOk();

        /* Assert */
        $this->assertEquals(Estimate::STATUS_REJECTED, $response->json()['data']['status']);
    }
}
