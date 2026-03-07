<?php

namespace Tests\Feature\Customer;

use App\Http\Controllers\V1\Customer\General\ProfileController;
use App\Http\Requests\Customer\CustomerProfileRequest;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
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
    public function it_validates_the_update_profile_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            ProfileController::class,
            'updateProfile',
            CustomerProfileRequest::class
        );
    }

    #[Test]
    public function it_updates_the_customer_profile(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $updatedCustomer = Customer::factory()->raw([
            'shipping' => ['name' => 'newName', 'address_street_1' => 'address'],
            'billing' => ['name' => 'newName', 'address_street_1' => 'address'],
        ]);

        /* Act */
        $this->postJson("api/v1/{$customer->company->slug}/customer/profile", $updatedCustomer)->assertOk();
        $customer->refresh();

        /* Assert */
        $this->assertSame('newName', $customer->shipping['name']);
        $this->assertSame('address', $customer->shipping['address_street_1']);
        $this->assertSame('newName', $customer->billing['name']);
        $this->assertSame('address', $customer->billing['address_street_1']);
    }

    #[Test]
    public function it_retrieves_the_authenticated_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();

        /* Act */
        $response = $this->getJson("api/v1/{$customer->company->slug}/customer/me");

        /* Assert */
        $response->assertOk();
    }
}
