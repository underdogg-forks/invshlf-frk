<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
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

        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer, ['*'], 'customer');
    }

    #[Test]
    public function it_retrieves_all_invoices_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();

        /* Act */
        $response = $this->getJson("api/v1/{$customer->company->slug}/customer/invoices?page=1");

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_retrieves_a_single_invoice_for_the_customer(): void
    {
        /* Arrange */
        $customer = Auth::guard('customer')->user();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        /* Act */
        $this->getJson("/api/v1/{$customer->company->slug}/customer/invoices/{$invoice->id}")->assertOk();

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
    }
}
