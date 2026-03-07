<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Payment\PaymentMethodsController;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_payment_methods(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/payment-methods?page=1');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_creates_a_payment_method(): void
    {
        /* Arrange */
        $data = [
            'name' => 'demo name',
            'company_id' => User::find(1)->companies()->first()->id,
        ];

        /* Act */
        $response = $this->postJson('api/v1/payment-methods', $data);

        /* Assert */
        $response->assertStatus(201);
        $this->assertDatabaseHas('payment_methods', [
            'name' => $data['name'],
            'company_id' => $data['company_id'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            PaymentMethodsController::class,
            'store',
            PaymentMethodRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_payment_method(): void
    {
        /* Arrange */
        $method = PaymentMethod::factory()->create();

        /* Act */
        $response = $this->getJson("api/v1/payment-methods/{$method->id}");

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('payment_methods', [
            'id' => $method->id,
            'name' => $method['name'],
            'company_id' => $method['company_id'],
        ]);
    }

    #[Test]
    public function it_updates_a_payment_method(): void
    {
        /* Arrange */
        $method = PaymentMethod::factory()->create();
        $data = ['name' => 'updated name'];

        /* Act */
        $response = $this->putJson("api/v1/payment-methods/{$method->id}", $data);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('payment_methods', ['id' => $method->id, 'name' => $data['name']]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            PaymentMethodsController::class,
            'update',
            PaymentMethodRequest::class
        );
    }

    #[Test]
    public function it_deletes_a_payment_method(): void
    {
        /* Arrange */
        $method = PaymentMethod::factory()->create();

        /* Act */
        $response = $this->deleteJson('api/v1/payment-methods/'.$method->id);

        /* Assert */
        $response->assertOk();
        $this->assertModelMissing($method);
    }
}
