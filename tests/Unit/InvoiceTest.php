<?php

namespace Tests\Unit;

use App\Http\Requests\InvoicesRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
    }

    #[Test]
    public function it_has_many_invoice_items(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->hasItems(5)->create();

        /* Act */
        $itemCount = $invoice->items()->count();

        /* Assert */
        $this->assertCount(5, $invoice->items);
        $this->assertEquals(5, $itemCount);
        $this->assertTrue($invoice->items()->exists());
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->hasTaxes(5)->create();

        /* Act */
        $taxCount = $invoice->taxes()->count();

        /* Assert */
        $this->assertCount(5, $invoice->taxes);
        $this->assertEquals(5, $taxCount);
        $this->assertTrue($invoice->taxes()->exists());
    }

    #[Test]
    public function it_has_many_payments(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->hasPayments(5)->create();

        /* Act */
        $paymentCount = $invoice->payments()->count();

        /* Assert */
        $this->assertCount(5, $invoice->payments);
        $this->assertEquals(5, $paymentCount);
        $this->assertTrue($invoice->payments()->exists());
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->forCustomer()->create();

        /* Act & Assert */
        $this->assertTrue($invoice->customer()->exists());
    }

    #[Test]
    public function it_returns_the_previous_status_as_draft_for_a_new_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();

        /* Act */
        $status = $invoice->getPreviousStatus();

        /* Assert */
        $this->assertEquals('DRAFT', $status);
    }

    #[Test]
    public function it_creates_an_invoice_with_items_and_taxes(): void
    {
        /* Arrange */
        $invoiceData = Invoice::factory()->raw();
        $item = InvoiceItem::factory()->raw();

        $invoiceData['items'] = [$item];
        $invoiceData['taxes'] = [Tax::factory()->raw()];

        $request = new InvoicesRequest;
        $request->replace($invoiceData);

        /* Act */
        $response = Invoice::createInvoice($request);

        /* Assert */
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $response->id,
            'name' => $item['name'],
            'description' => $item['description'],
            'total' => $item['total'],
            'quantity' => $item['quantity'],
            'discount' => $item['discount'],
            'price' => $item['price'],
        ]);
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $invoiceData['invoice_number'],
            'sub_total' => $invoiceData['sub_total'],
            'total' => $invoiceData['total'],
            'tax' => $invoiceData['tax'],
            'discount' => $invoiceData['discount'],
            'notes' => $invoiceData['notes'],
            'customer_id' => $invoiceData['customer_id'],
            'template_name' => $invoiceData['template_name'],
        ]);
    }

    #[Test]
    public function it_updates_an_invoice_with_new_items_and_taxes(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        $newInvoiceData = Invoice::factory()->raw();
        $item = InvoiceItem::factory()->raw(['invoice_id' => $invoice->id]);
        $tax = Tax::factory()->raw(['invoice_id' => $invoice->id]);

        $newInvoiceData['items'] = [$item];
        $newInvoiceData['taxes'] = [$tax];

        $request = new InvoicesRequest;
        $request->replace($newInvoiceData);

        /* Act */
        $response = $invoice->updateInvoice($request);

        /* Assert */
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $response->id,
            'name' => $item['name'],
            'description' => $item['description'],
            'total' => $item['total'],
            'quantity' => $item['quantity'],
            'discount' => $item['discount'],
            'price' => $item['price'],
        ]);
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $newInvoiceData['invoice_number'],
            'sub_total' => $newInvoiceData['sub_total'],
            'total' => $newInvoiceData['total'],
            'tax' => $newInvoiceData['tax'],
            'discount' => $newInvoiceData['discount'],
            'notes' => $newInvoiceData['notes'],
            'customer_id' => $newInvoiceData['customer_id'],
            'template_name' => $newInvoiceData['template_name'],
        ]);
    }

    #[Test]
    public function it_creates_items_for_an_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->raw(['invoice_id' => $invoice->id]);
        $request = new InvoicesRequest;
        $request->replace(['items' => [$item]]);

        /* Act */
        Invoice::createItems($invoice, $request->items);

        /* Assert */
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => $item['description'],
            'price' => $item['price'],
            'tax' => $item['tax'],
            'quantity' => $item['quantity'],
            'total' => $item['total'],
        ]);
    }

    #[Test]
    public function it_creates_taxes_for_an_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        $tax = Tax::factory()->raw(['invoice_id' => $invoice->id]);
        $request = new Request;
        $request->replace(['taxes' => [$tax]]);

        /* Act */
        Invoice::createTaxes($invoice, $request->taxes);

        /* Assert */
        $this->assertDatabaseHas('taxes', [
            'invoice_id' => $invoice->id,
            'name' => $tax['name'],
            'amount' => $tax['amount'],
        ]);
    }
}
