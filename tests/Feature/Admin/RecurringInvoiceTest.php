<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\RecurringInvoice\RecurringInvoiceController;
use App\Http\Requests\RecurringInvoiceRequest;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecurringInvoiceTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_recurring_invoices(): void
    {
        /* Arrange */
        RecurringInvoice::factory()->create();

        /* Act */
        $response = $this->getJson('api/v1/recurring-invoices?page=1');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            RecurringInvoiceController::class,
            'store',
            RecurringInvoiceRequest::class
        );
    }

    #[Test]
    public function it_creates_a_recurring_invoice(): void
    {
        /* Arrange */
        $recurringInvoice = RecurringInvoice::factory()->raw();
        $recurringInvoice['items'] = [InvoiceItem::factory()->raw()];

        /* Act */
        $this->postJson('api/v1/recurring-invoices', $recurringInvoice)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('recurring_invoices', collect($recurringInvoice)->only(['frequency'])->toArray());
    }

    #[Test]
    public function it_retrieves_a_single_recurring_invoice(): void
    {
        /* Arrange */
        $recurringInvoice = RecurringInvoice::factory()->create();

        /* Act & Assert */
        $this->getJson("api/v1/recurring-invoices/{$recurringInvoice->id}")->assertOk();
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            RecurringInvoiceController::class,
            'update',
            RecurringInvoiceRequest::class
        );
    }

    #[Test]
    public function it_updates_a_recurring_invoice(): void
    {
        /* Arrange */
        $recurringInvoice = RecurringInvoice::factory()->create();
        $recurringInvoice['items'] = [InvoiceItem::factory()->raw()];

        $updatedData = RecurringInvoice::factory()->raw();
        $updatedData['items'] = [InvoiceItem::factory()->raw()];

        /* Act */
        $this->putJson("api/v1/recurring-invoices/{$recurringInvoice->id}", $updatedData)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('recurring_invoices', collect($updatedData)->only(['frequency'])->toArray());
    }

    #[Test]
    public function it_deletes_multiple_recurring_invoices(): void
    {
        /* Arrange */
        $recurringInvoices = RecurringInvoice::factory()->count(3)->create();
        $data = ['ids' => $recurringInvoices->pluck('id')];

        /* Act */
        $this->postJson('api/v1/recurring-invoices/delete', $data)
            ->assertOk()
            ->assertJson(['success' => true]);

        /* Assert */
        foreach ($recurringInvoices as $recurringInvoice) {
            $this->assertModelMissing($recurringInvoice);
        }
    }

    #[Test]
    public function it_calculates_the_frequency_for_a_recurring_invoice(): void
    {
        /* Arrange */
        $data = [
            'frequency' => '* * 2 * *',
            'starts_at' => Carbon::now()->format('Y-m-d'),
        ];

        /* Act & Assert */
        $this->getJson('api/v1/recurring-invoice-frequency?'.http_build_query($data, '', '&'))->assertOk();
    }
}
