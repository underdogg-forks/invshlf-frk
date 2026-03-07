<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Invoice\InvoicesController;
use App\Http\Requests\InvoicesRequest;
use App\Mail\SendInvoiceMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_invoices(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/invoices?page=1&type=OVERDUE&limit=20');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_creates_an_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'taxes' => [Tax::factory()->raw()],
            'items' => [InvoiceItem::factory()->raw()],
        ]);

        /* Act */
        $response = $this->postJson('api/v1/invoices', $invoice);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('invoices', [
            'template_name' => $invoice['template_name'],
            'invoice_number' => $invoice['invoice_number'],
            'sub_total' => $invoice['sub_total'],
            'discount' => $invoice['discount'],
            'customer_id' => $invoice['customer_id'],
            'total' => $invoice['total'],
            'tax' => $invoice['tax'],
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'item_id' => $invoice['items'][0]['item_id'],
            'name' => $invoice['items'][0]['name'],
        ]);
    }

    #[Test]
    public function it_creates_an_invoice_with_negative_and_zero_item_quantities(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'items' => [
                InvoiceItem::factory()->raw(['quantity' => -2, 'price' => 100]),
                InvoiceItem::factory()->raw(['quantity' => 1, 'price' => 50]),
                InvoiceItem::factory()->raw(['quantity' => 0, 'price' => 75]),
            ],
            'sub_total' => -150,
            'total' => -150,
        ]);

        /* Act */
        $response = $this->postJson('api/v1/invoices', $invoice);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('invoices', ['total' => -150, 'sub_total' => -150]);

        $createdInvoice = Invoice::where('total', -150)->first();
        $this->assertNotNull($createdInvoice);
        $this->assertEquals(3, $createdInvoice->items()->count());

        $expectedItems = [
            ['quantity' => -2, 'total' => -200],
            ['quantity' => 1, 'total' => 50],
            ['quantity' => 0, 'total' => 0],
        ];

        foreach ($expectedItems as $expected) {
            $item = $createdInvoice->items()->where('quantity', $expected['quantity'])->first();
            $this->assertNotNull($item);
            $this->assertEquals($expected['total'], $item->total);
        }
    }

    #[Test]
    public function it_creates_an_invoice_with_sent_status(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'taxes' => [Tax::factory()->raw()],
            'items' => [InvoiceItem::factory()->raw()],
        ]);

        /* Act */
        $response = $this->postJson('api/v1/invoices', $invoice);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $invoice['invoice_number'],
            'sub_total' => $invoice['sub_total'],
            'total' => $invoice['total'],
            'tax' => $invoice['tax'],
            'discount' => $invoice['discount'],
            'customer_id' => $invoice['customer_id'],
            'template_name' => $invoice['template_name'],
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'item_id' => $invoice['items'][0]['item_id'],
            'name' => $invoice['items'][0]['name'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            InvoicesController::class,
            'store',
            InvoicesRequest::class
        );
    }

    #[Test]
    public function it_updates_an_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create([
            'invoice_date' => '1988-07-18',
            'due_date' => '1988-08-18',
        ]);
        $updatedInvoice = Invoice::factory()->raw([
            'taxes' => [Tax::factory()->raw()],
            'items' => [InvoiceItem::factory()->raw()],
        ]);

        /* Act */
        $this->putJson('api/v1/invoices/'.$invoice->id, $updatedInvoice)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $updatedInvoice['invoice_number'],
            'sub_total' => $updatedInvoice['sub_total'],
            'total' => $updatedInvoice['total'],
            'tax' => $updatedInvoice['tax'],
            'discount' => $updatedInvoice['discount'],
            'customer_id' => $updatedInvoice['customer_id'],
            'template_name' => $updatedInvoice['template_name'],
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'item_id' => $updatedInvoice['items'][0]['item_id'],
            'name' => $updatedInvoice['items'][0]['name'],
        ]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            InvoicesController::class,
            'update',
            InvoicesRequest::class
        );
    }

    #[Test]
    public function it_sends_an_invoice_to_a_customer_via_email(): void
    {
        /* Arrange */
        Mail::fake();
        $invoice = Invoice::factory()->create([
            'invoice_date' => '1988-07-18',
            'due_date' => '1988-08-18',
        ]);
        $data = [
            'from' => 'john@example.com',
            'to' => 'doe@example.com',
            'subject' => 'email subject',
            'body' => 'email body',
        ];

        /* Act */
        $response = $this->postJson('api/v1/invoices/'.$invoice->id.'/send', $data);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(Invoice::STATUS_SENT, Invoice::find($invoice->id)->status);
        Mail::assertSent(SendInvoiceMail::class);
    }

    #[Test]
    public function it_marks_an_invoice_as_paid(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create([
            'invoice_date' => '1988-07-18',
            'due_date' => '1988-08-18',
        ]);
        $data = ['status' => Invoice::STATUS_COMPLETED];

        /* Act */
        $response = $this->postJson('api/v1/invoices/'.$invoice->id.'/status', $data);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(Invoice::STATUS_PAID, Invoice::find($invoice->id)->paid_status);
    }

    #[Test]
    public function it_marks_an_invoice_as_sent(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create([
            'invoice_date' => '1988-07-18',
            'due_date' => '1988-08-18',
        ]);
        $data = ['status' => Invoice::STATUS_SENT];

        /* Act */
        $response = $this->postJson('api/v1/invoices/'.$invoice->id.'/status', $data);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(Invoice::STATUS_SENT, Invoice::find($invoice->id)->status);
    }

    #[Test]
    public function it_searches_invoices_by_filters(): void
    {
        /* Arrange */
        $filters = [
            'page' => 1,
            'limit' => 15,
            'search' => 'doe',
            'status' => Invoice::STATUS_DRAFT,
            'from_date' => '2019-01-20',
            'to_date' => '2019-01-27',
            'invoice_number' => '000012',
        ];

        /* Act */
        $response = $this->getJson('api/v1/invoices?'.http_build_query($filters, '', '&'));

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_deletes_multiple_invoices(): void
    {
        /* Arrange */
        $invoices = Invoice::factory()->count(3)->create([
            'invoice_date' => '1988-07-18',
            'due_date' => '1988-08-18',
        ]);
        $data = ['ids' => $invoices->pluck('id')];

        /* Act */
        $this->postJson('api/v1/invoices/delete', $data)
            ->assertOk()
            ->assertJson(['success' => true]);

        /* Assert */
        foreach ($invoices as $invoice) {
            $this->assertModelMissing($invoice);
        }
    }

    #[Test]
    public function it_clones_an_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create([
            'invoice_date' => '1988-07-18',
            'due_date' => '1988-08-18',
        ]);

        /* Act & Assert */
        $this->postJson("api/v1/invoices/{$invoice->id}/clone")->assertStatus(201);
    }

    #[Test]
    public function it_creates_an_invoice_with_a_negative_tax(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'taxes' => [Tax::factory()->raw(['percent' => -9.99])],
            'items' => [InvoiceItem::factory()->raw()],
        ]);

        /* Act */
        $response = $this->postJson('api/v1/invoices', $invoice);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $invoice['invoice_number'],
            'sub_total' => $invoice['sub_total'],
            'total' => $invoice['total'],
            'tax' => $invoice['tax'],
            'discount' => $invoice['discount'],
            'customer_id' => $invoice['customer_id'],
        ]);
        $this->assertDatabaseHas('invoice_items', ['name' => $invoice['items'][0]['name']]);
        $this->assertDatabaseHas('taxes', ['tax_type_id' => $invoice['taxes'][0]['tax_type_id']]);
    }

    #[Test]
    public function it_creates_an_invoice_with_tax_per_item(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'tax_per_item' => 'YES',
            'items' => [
                InvoiceItem::factory()->raw(['taxes' => [Tax::factory()->raw()]]),
                InvoiceItem::factory()->raw(['taxes' => [Tax::factory()->raw()]]),
            ],
        ]);

        /* Act */
        $response = $this->postJson('api/v1/invoices', $invoice);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $invoice['invoice_number'],
            'sub_total' => $invoice['sub_total'],
            'total' => $invoice['total'],
            'tax' => $invoice['tax'],
            'discount' => $invoice['discount'],
            'customer_id' => $invoice['customer_id'],
        ]);
        $this->assertDatabaseHas('invoice_items', ['name' => $invoice['items'][0]['name']]);
        $this->assertDatabaseHas('taxes', [
            'tax_type_id' => $invoice['items'][0]['taxes'][0]['tax_type_id'],
        ]);
    }

    #[Test]
    public function it_creates_an_invoice_with_foreign_currency(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'discount_type' => 'fixed',
            'discount_val' => 20,
            'sub_total' => 100,
            'total' => 84,
            'tax' => 4,
            'due_amount' => 84,
            'exchange_rate' => 86.403538,
            'base_discount_val' => 1728.07,
            'base_sub_total' => 8640.35,
            'base_total' => 7257.90,
            'base_tax' => 345.61,
            'base_due_amount' => 7257.90,
            'taxes' => [Tax::factory()->raw([
                'amount' => 4,
                'percent' => 5,
                'base_amount' => 345.61,
            ])],
            'items' => [InvoiceItem::factory()->raw([
                'discount_type' => 'fixed',
                'price' => 100,
                'quantity' => 1,
                'discount' => 0,
                'discount_val' => 0,
                'tax' => 0,
                'total' => 100,
                'base_price' => 8640.35,
                'exchange_rate' => 86.403538,
                'base_discount_val' => 0,
                'base_tax' => 0,
                'base_total' => 8640.35,
            ])],
        ]);

        /* Act */
        $this->postJson('api/v1/invoices', $invoice)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'template_name' => $invoice['template_name'],
            'invoice_number' => $invoice['invoice_number'],
            'sub_total' => $invoice['sub_total'],
            'discount' => $invoice['discount'],
            'customer_id' => $invoice['customer_id'],
            'total' => $invoice['total'],
            'tax' => $invoice['tax'],
        ]);
        $this->assertDatabaseHas('taxes', [
            'tax_type_id' => $invoice['taxes'][0]['tax_type_id'],
            'amount' => $invoice['tax'],
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'item_id' => $invoice['items'][0]['item_id'],
            'name' => $invoice['items'][0]['name'],
        ]);
    }

    #[Test]
    public function it_updates_an_invoice_with_foreign_currency(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()
            ->hasItems(1)
            ->hasTaxes(1)
            ->create(['invoice_date' => '1988-07-18', 'due_date' => '1988-08-18']);

        $updatedInvoice = Invoice::factory()->raw([
            'id' => $invoice['id'],
            'discount_type' => 'fixed',
            'discount_val' => 20,
            'sub_total' => 100,
            'total' => 84,
            'tax' => 4,
            'due_amount' => 84,
            'exchange_rate' => 86.403538,
            'base_discount_val' => 1728.07,
            'base_sub_total' => 8640.35,
            'base_total' => 7257.897192,
            'base_tax' => 345.614152,
            'base_due_amount' => 7257.897192,
            'taxes' => [Tax::factory()->raw([
                'tax_type_id' => $invoice->taxes[0]->tax_type_id,
                'amount' => 4,
                'percent' => 5,
                'base_amount' => 345.614152,
            ])],
            'items' => [InvoiceItem::factory()->raw([
                'invoice_id' => $invoice->id,
                'discount_type' => 'fixed',
                'price' => 100,
                'quantity' => 1,
                'discount' => 0,
                'discount_val' => 0,
                'tax' => 0,
                'total' => 100,
                'base_price' => 8640.3538,
                'exchange_rate' => 86.403538,
                'base_discount_val' => 0,
                'base_tax' => 0,
                'base_total' => 8640.3538,
            ])],
        ]);

        /* Act */
        $this->putJson('api/v1/invoices/'.$invoice->id, $updatedInvoice)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice['id'],
            'invoice_number' => $updatedInvoice['invoice_number'],
            'sub_total' => $updatedInvoice['sub_total'],
            'total' => $updatedInvoice['total'],
            'tax' => $updatedInvoice['tax'],
            'discount' => $updatedInvoice['discount'],
            'customer_id' => $updatedInvoice['customer_id'],
            'template_name' => $updatedInvoice['template_name'],
            'exchange_rate' => $updatedInvoice['exchange_rate'],
            'base_total' => $updatedInvoice['base_total'],
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $updatedInvoice['items'][0]['invoice_id'],
            'item_id' => $updatedInvoice['items'][0]['item_id'],
            'name' => $updatedInvoice['items'][0]['name'],
            'exchange_rate' => $updatedInvoice['items'][0]['exchange_rate'],
            'base_price' => $updatedInvoice['items'][0]['base_price'],
            'base_total' => $updatedInvoice['items'][0]['base_total'],
        ]);
        $this->assertDatabaseHas('taxes', [
            'amount' => $updatedInvoice['taxes'][0]['amount'],
            'name' => $updatedInvoice['taxes'][0]['name'],
            'base_amount' => $updatedInvoice['taxes'][0]['base_amount'],
        ]);
    }

    #[Test]
    public function it_creates_an_invoice_with_tax_included(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->raw([
            'taxes' => [Tax::factory()->raw()],
            'items' => [InvoiceItem::factory()->raw()],
            'tax_included' => true,
        ]);

        /* Act */
        $response = $this->postJson('api/v1/invoices', $invoice);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('invoices', ['tax_included' => true]);
    }
}
