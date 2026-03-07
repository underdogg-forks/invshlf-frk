<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Customer\CustomersController;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_customers(): void
    {
        // Arrange - data seeded in setUp

        // Act
        $response = $this->getJson('api/v1/customers?page=1');

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_retrieves_stats_for_a_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create();
        Invoice::factory()->create(['customer_id' => $customer->id]);

        // Act
        $response = $this->getJson("api/v1/customers/{$customer->id}/stats");

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function it_creates_a_customer_with_billing_and_shipping_addresses(): void
    {
        // Arrange
        $customer = Customer::factory()->raw([
            'shipping' => ['name' => 'newName', 'address_street_1' => 'address'],
            'billing' => ['name' => 'newName', 'address_street_1' => 'address'],
        ]);

        // Act
        $this->postJson('api/v1/customers', $customer)->assertOk();

        // Assert
        $this->assertDatabaseHas('customers', [
            'name' => $customer['name'],
            'email' => $customer['email'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            CustomersController::class,
            'store',
            CustomerRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create();

        // Act
        $response = $this->getJson("api/v1/customers/{$customer->id}");

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => $customer['name'],
            'email' => $customer['email'],
        ]);
    }

    #[Test]
    public function it_updates_a_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create();
        $updatedCustomer = Customer::factory()->raw([
            'shipping' => ['name' => 'newName', 'address_street_1' => 'address'],
            'billing' => ['name' => 'newName', 'address_street_1' => 'address'],
        ]);

        // Act
        $response = $this->putJson('api/v1/customers/'.$customer->id, $updatedCustomer);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('customers', collect($updatedCustomer)
            ->only(['email'])
            ->merge(['creator_id' => Auth::id()])
            ->toArray());
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            CustomersController::class,
            'update',
            CustomerRequest::class
        );
    }

    #[Test]
    public function it_searches_customers_by_filters(): void
    {
        // Arrange
        $filters = ['page' => 1, 'limit' => 15, 'search' => 'doe', 'email' => '.com'];

        // Act
        $response = $this->getJson('api/v1/customers?'.http_build_query($filters, '', '&'));

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_deletes_multiple_customers(): void
    {
        // Arrange
        $customers = Customer::factory()->count(4)->create();
        $data = ['ids' => $customers->pluck('id')];

        // Act
        $response = $this->postJson('api/v1/customers/delete', $data);

        // Assert
        $response->assertOk()->assertJson(['success' => true]);
    }
}
