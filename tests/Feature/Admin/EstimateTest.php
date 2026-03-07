<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Estimate\EstimatesController;
use App\Http\Controllers\V1\Admin\Estimate\SendEstimateController;
use App\Http\Requests\DeleteEstimatesRequest;
use App\Http\Requests\EstimatesRequest;
use App\Http\Requests\SendEstimatesRequest;
use App\Mail\SendEstimateMail;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
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

        $user = User::find(1);
        $this->withHeaders(['company' => $user->companies()->first()->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_retrieves_a_paginated_list_of_estimates(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/estimates?page=1');

        /* Assert */
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    {
        /* Arrange */
        $estimate = Estimate::factory()->raw([
            'estimate_number' => 'EST-000006',
            'items' => [EstimateItem::factory()->raw()],
            'taxes' => [Tax::factory()->raw()],
        ]);

        /* Act */
        $this->postJson('api/v1/estimates', $estimate)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('estimates', [
            'template_name' => $estimate['template_name'],
            'estimate_number' => $estimate['estimate_number'],
            'discount_type' => $estimate['discount_type'],
            'discount_val' => $estimate['discount_val'],
            'sub_total' => $estimate['sub_total'],
            'discount' => $estimate['discount'],
            'customer_id' => $estimate['customer_id'],
            'total' => $estimate['total'],
            'notes' => $estimate['notes'],
            'tax' => $estimate['tax'],
        ]);
    }

    #[Test]
    public function it_clones_an_estimate(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->create();
        $beforeCount = Estimate::count();

        /* Act */
        $this->post("/api/v1/estimates/{$estimate->id}/clone");

        /* Assert */
        $this->assertDatabaseCount('estimates', $beforeCount + 1);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            EstimatesController::class,
            'store',
            EstimatesRequest::class
        );
    }

    #[Test]
    public function it_updates_an_estimate(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()
            ->hasItems(1)
            ->hasTaxes(1)
            ->create(['estimate_date' => '1988-07-18', 'expiry_date' => '1988-08-18']);

        $updatedEstimate = Estimate::factory()->raw([
            'items' => [EstimateItem::factory()->raw(['estimate_id' => $estimate->id])],
            'taxes' => [Tax::factory()->raw(['tax_type_id' => $estimate->taxes[0]->tax_type_id])],
        ]);

        /* Act */
        $response = $this->putJson('api/v1/estimates/'.$estimate->id, $updatedEstimate);

        /* Assert */
        $this->assertDatabaseHas('estimates', [
            'template_name' => $updatedEstimate['template_name'],
            'estimate_number' => $updatedEstimate['estimate_number'],
            'discount_type' => $updatedEstimate['discount_type'],
            'discount_val' => $updatedEstimate['discount_val'],
            'sub_total' => $updatedEstimate['sub_total'],
            'discount' => $updatedEstimate['discount'],
            'customer_id' => $updatedEstimate['customer_id'],
            'total' => $updatedEstimate['total'],
            'notes' => $updatedEstimate['notes'],
            'tax' => $updatedEstimate['tax'],
        ]);
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $updatedEstimate['items'][0]['estimate_id'],
        ]);
        $response->assertStatus(200);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            EstimatesController::class,
            'update',
            EstimatesRequest::class
        );
    }

    #[Test]
    public function it_searches_estimates_by_filters(): void
    {
        /* Arrange */
        $filters = [
            'page' => 1,
            'limit' => 15,
            'search' => 'doe',
            'from_date' => '2020-07-18',
            'to_date' => '2020-07-20',
            'estimate_number' => '000003',
        ];

        /* Act */
        $response = $this->getJson('api/v1/estimates?'.http_build_query($filters, '', '&'));

        /* Assert */
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            SendEstimateController::class,
            '__invoke',
            SendEstimatesRequest::class
        );
    }

    #[Test]
    public function it_sends_an_estimate_to_a_customer_via_email(): void
    {
        /* Arrange */
        Mail::fake();
        $estimate = Estimate::factory()->create([
            'estimate_date' => '1988-07-18',
            'expiry_date' => '1988-08-18',
        ]);
        $data = [
            'subject' => 'test',
            'body' => 'test',
            'from' => 'john@example.com',
            'to' => 'doe@example.com',
        ];

        /* Act */
        $response = $this->postJson("api/v1/estimates/{$estimate->id}/send", $data);

        /* Assert */
        $response->assertStatus(200)->assertJson(['success' => true]);
        Mail::assertSent(SendEstimateMail::class);
    }

    #[Test]
    public function it_marks_an_estimate_as_accepted(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->create([
            'estimate_date' => '1988-07-18',
            'expiry_date' => '1988-08-18',
        ]);
        $data = ['status' => Estimate::STATUS_ACCEPTED];

        /* Act */
        $response = $this->postJson("api/v1/estimates/{$estimate->id}/status", $data);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(Estimate::STATUS_ACCEPTED, Estimate::find($estimate->id)->status);
    }

    #[Test]
    public function it_marks_an_estimate_as_rejected(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->create([
            'estimate_date' => '1988-07-18',
            'expiry_date' => '1988-08-18',
        ]);
        $data = ['status' => Estimate::STATUS_REJECTED];

        /* Act */
        $response = $this->postJson("api/v1/estimates/{$estimate->id}/status", $data);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(Estimate::STATUS_REJECTED, Estimate::find($estimate->id)->status);
    }

    #[Test]
    public function it_converts_an_estimate_to_an_invoice(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->create([
            'estimate_date' => now(),
            'expiry_date' => now()->addMonth(),
        ]);

        /* Act */
        $response = $this->postJson("api/v1/estimates/{$estimate->id}/convert-to-invoice");

        /* Assert */
        if ($response->status() !== 200) {
            $this->fail('Response status is not 200. Response body: '.json_encode($response->json()));
        }
        $response->assertStatus(200);
    }

    #[Test]
    public function it_validates_the_delete_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            EstimatesController::class,
            'delete',
            DeleteEstimatesRequest::class
        );
    }

    #[Test]
    public function it_deletes_multiple_estimates(): void
    {
        /* Arrange */
        $estimates = Estimate::factory()->count(3)->create([
            'estimate_date' => '1988-07-18',
            'expiry_date' => '1988-08-18',
        ]);
        $data = ['ids' => $estimates->pluck('id')];

        /* Act */
        $response = $this->postJson('api/v1/estimates/delete', $data);

        /* Assert */
        $response->assertStatus(200)->assertJson(['success' => true]);
        foreach ($estimates as $estimate) {
            $this->assertModelMissing($estimate);
        }
    }

    #[Test]
    public function it_retrieves_available_estimate_templates(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->getJson('api/v1/estimates/templates')
            ->assertStatus(200)
            ->assertJsonStructure(['estimateTemplates']);
    }

    #[Test]
    public function it_creates_an_estimate_with_tax_per_item(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->raw([
            'estimate_number' => 'EST-000006',
            'tax_per_item' => 'YES',
            'items' => [
                EstimateItem::factory()->raw(['taxes' => [Tax::factory()->raw()]]),
                EstimateItem::factory()->raw(['taxes' => [Tax::factory()->raw()]]),
            ],
        ]);

        /* Act */
        $this->postJson('api/v1/estimates', $estimate)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('estimates', [
            'template_name' => $estimate['template_name'],
            'estimate_number' => $estimate['estimate_number'],
            'discount_type' => $estimate['discount_type'],
            'discount_val' => $estimate['discount_val'],
            'sub_total' => $estimate['sub_total'],
            'discount' => $estimate['discount'],
            'customer_id' => $estimate['customer_id'],
            'total' => $estimate['total'],
            'notes' => $estimate['notes'],
            'tax' => $estimate['tax'],
        ]);
        $this->assertDatabaseHas('estimate_items', ['name' => $estimate['items'][0]['name']]);
        $this->assertDatabaseHas('taxes', [
            'tax_type_id' => $estimate['items'][0]['taxes'][0]['tax_type_id'],
        ]);
    }

    #[Test]
    public function it_creates_an_estimate_with_foreign_currency(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->raw([
            'discount_type' => 'fixed',
            'discount_val' => 20,
            'sub_total' => 200,
            'total' => 189,
            'tax' => 9,
            'exchange_rate' => 86.403538,
            'base_discount_val' => 1728.07,
            'base_sub_total' => 17280.71,
            'base_total' => 16330.27,
            'base_tax' => 777.63,
            'taxes' => [Tax::factory()->raw([
                'amount' => 9,
                'percent' => 5,
                'exchange_rate' => 86.403538,
                'base_amount' => 777.63,
            ])],
            'items' => [EstimateItem::factory()->raw([
                'discount_type' => 'fixed',
                'quantity' => 1,
                'discount' => 0,
                'discount_val' => 0,
                'price' => 200,
                'tax' => 0,
                'total' => 200,
                'exchange_rate' => 86.403538,
                'base_discount_val' => 0,
                'base_price' => 17280.71,
                'base_tax' => 777.63,
                'base_total' => 17280.71,
            ])],
        ]);

        /* Act */
        $this->postJson('api/v1/estimates', $estimate)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('estimates', [
            'template_name' => $estimate['template_name'],
            'estimate_number' => $estimate['estimate_number'],
            'discount_type' => $estimate['discount_type'],
            'discount_val' => $estimate['discount_val'],
            'sub_total' => $estimate['sub_total'],
            'discount' => $estimate['discount'],
            'customer_id' => $estimate['customer_id'],
            'total' => $estimate['total'],
            'notes' => $estimate['notes'],
            'tax' => $estimate['tax'],
        ]);
        $this->assertDatabaseHas('taxes', [
            'tax_type_id' => $estimate['taxes'][0]['tax_type_id'],
            'amount' => $estimate['tax'],
        ]);
        $this->assertDatabaseHas('estimate_items', [
            'item_id' => $estimate['items'][0]['item_id'],
            'name' => $estimate['items'][0]['name'],
        ]);
    }

    #[Test]
    public function it_updates_an_estimate_with_foreign_currency(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()
            ->hasItems(1)
            ->hasTaxes(1)
            ->create(['estimate_date' => '1988-07-18', 'expiry_date' => '1988-08-18']);

        $updatedEstimate = Estimate::factory()->raw([
            'id' => $estimate->id,
            'discount_type' => 'fixed',
            'discount_val' => 20,
            'sub_total' => 200,
            'total' => 189,
            'tax' => 9,
            'exchange_rate' => 86.403538,
            'base_discount_val' => 1728.07076,
            'base_sub_total' => 17280.7076,
            'base_total' => 16330.268682,
            'base_tax' => 777.631842,
            'taxes' => [Tax::factory()->raw([
                'tax_type_id' => $estimate->taxes[0]->tax_type_id,
                'amount' => 9,
                'percent' => 5,
                'exchange_rate' => 86.403538,
                'base_amount' => 777.631842,
            ])],
            'items' => [EstimateItem::factory()->raw([
                'estimate_id' => $estimate->id,
                'discount_type' => 'fixed',
                'quantity' => 1,
                'discount' => 0,
                'discount_val' => 0,
                'price' => 200,
                'tax' => 0,
                'total' => 200,
                'exchange_rate' => 86.403538,
                'base_discount_val' => 0,
                'base_price' => 17280.7076,
                'base_tax' => 777.631842,
                'base_total' => 17280.7076,
            ])],
        ]);

        /* Act */
        $response = $this->putJson('api/v1/estimates/'.$estimate->id, $updatedEstimate);

        /* Assert */
        $this->assertDatabaseHas('estimates', [
            'id' => $estimate['id'],
            'template_name' => $updatedEstimate['template_name'],
            'estimate_number' => $updatedEstimate['estimate_number'],
            'discount_type' => $updatedEstimate['discount_type'],
            'discount_val' => $updatedEstimate['discount_val'],
            'sub_total' => $updatedEstimate['sub_total'],
            'discount' => $updatedEstimate['discount'],
            'customer_id' => $updatedEstimate['customer_id'],
            'total' => $updatedEstimate['total'],
            'tax' => $updatedEstimate['tax'],
            'exchange_rate' => $updatedEstimate['exchange_rate'],
            'base_discount_val' => $updatedEstimate['base_discount_val'],
            'base_sub_total' => $updatedEstimate['base_sub_total'],
            'base_total' => $updatedEstimate['base_total'],
            'base_tax' => $updatedEstimate['base_tax'],
        ]);
        $this->assertDatabaseHas('estimate_items', [
            'estimate_id' => $updatedEstimate['items'][0]['estimate_id'],
            'exchange_rate' => $updatedEstimate['items'][0]['exchange_rate'],
            'base_price' => $updatedEstimate['items'][0]['base_price'],
            'base_discount_val' => $updatedEstimate['items'][0]['base_discount_val'],
            'base_tax' => $updatedEstimate['items'][0]['base_tax'],
            'base_total' => $updatedEstimate['items'][0]['base_total'],
        ]);
        $response->assertStatus(200);
    }

    #[Test]
    public function it_creates_an_estimate_with_tax_included(): void
    {
        /* Arrange */
        $estimate = Estimate::factory()->raw([
            'estimate_number' => 'EST-000006',
            'items' => [EstimateItem::factory()->raw()],
            'taxes' => [Tax::factory()->raw()],
            'tax_included' => true,
        ]);

        /* Act */
        $this->postJson('api/v1/estimates', $estimate)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('estimates', [
            'tax_included' => $estimate['tax_included'],
        ]);
    }
}
