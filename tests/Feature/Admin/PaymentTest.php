<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Payment\PaymentsController;
use App\Http\Requests\PaymentRequest;
use App\Mail\SendPaymentMail;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTest extends TestCase
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
    public function it_retrieves_a_paginated_list_of_payments(): void
    {
        // Arrange - data seeded in setUp

        // Act
        $response = $this->getJson('api/v1/payments?page=1');

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_retrieves_a_single_payment(): void
    {
        // Arrange
        $payment = Payment::factory()->create();

        // Act
        $response = $this->getJson("api/v1/payments/{$payment->id}");

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    public function it_creates_a_payment_for_an_invoice(): void
    {
        // Arrange
        $invoice = Invoice::factory()->create(['due_amount' => 100, 'exchange_rate' => 1]);
        $payment = Payment::factory()->raw([
            'invoice_id' => $invoice->id,
            'payment_number' => 'PAY-000001',
            'amount' => $invoice->due_amount,
            'exchange_rate' => 1,
        ]);

        // Act
        $response = $this->postJson('api/v1/payments', $payment);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('payments', [
            'payment_number' => $payment['payment_number'],
            'customer_id' => $payment['customer_id'],
            'amount' => $payment['amount'],
            'company_id' => $payment['company_id'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            PaymentsController::class,
            'store',
            PaymentRequest::class
        );
    }

    #[Test]
    public function it_updates_a_payment(): void
    {
        // Arrange
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create([
            'payment_date' => '1988-08-18',
            'invoice_id' => $invoice->id,
            'exchange_rate' => 1,
        ]);
        $updatedPayment = Payment::factory()->raw([
            'invoice_id' => $invoice->id,
            'exchange_rate' => 1,
        ]);

        // Act
        $this->putJson("api/v1/payments/{$payment->id}", $updatedPayment)->assertOk();

        // Assert
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_number' => $updatedPayment['payment_number'],
            'customer_id' => $updatedPayment['customer_id'],
            'amount' => $updatedPayment['amount'],
        ]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            PaymentsController::class,
            'update',
            PaymentRequest::class
        );
    }

    #[Test]
    public function it_searches_payments_by_filters(): void
    {
        // Arrange
        $filters = [
            'page' => 1,
            'limit' => 15,
            'search' => 'doe',
            'payment_number' => 'PAY-000001',
            'payment_mode' => 'OTHER',
        ];

        // Act
        $response = $this->getJson('api/v1/payments?'.http_build_query($filters, '', '&'));

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_sends_a_payment_receipt_to_a_customer_via_email(): void
    {
        // Arrange
        Mail::fake();
        $payment = Payment::factory()->create();
        $data = [
            'subject' => 'test',
            'body' => 'test',
            'from' => 'john@example.com',
            'to' => 'doe@example.com',
        ];

        // Act
        $response = $this->postJson("api/v1/payments/{$payment->id}/send", $data);

        // Assert
        $response->assertJson(['success' => true]);
        Mail::assertSent(SendPaymentMail::class);
    }

    #[Test]
    public function it_deletes_multiple_payments(): void
    {
        // Arrange
        $payments = Payment::factory()->count(5)->create();
        $data = ['ids' => $payments->pluck('id')];

        // Act
        $response = $this->postJson('api/v1/payments/delete', $data);

        // Assert
        $response->assertJson(['success' => true]);
    }

    #[Test]
    public function it_creates_a_payment_without_an_invoice(): void
    {
        // Arrange
        $payment = Payment::factory()->raw([
            'payment_number' => 'PAY-000001',
            'exchange_rate' => 1,
        ]);

        // Act
        $this->postJson('api/v1/payments', $payment)->assertOk();

        // Assert
        $this->assertDatabaseHas('payments', [
            'payment_number' => $payment['payment_number'],
            'customer_id' => $payment['customer_id'],
            'amount' => $payment['amount'],
            'company_id' => $payment['company_id'],
        ]);
    }

    #[Test]
    public function it_creates_a_payment_linked_to_an_invoice(): void
    {
        // Arrange
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->raw([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->due_amount,
            'exchange_rate' => 1,
        ]);

        // Act
        $this->postJson('api/v1/payments', $payment)->assertOk();

        // Assert
        $this->assertDatabaseHas('payments', [
            'payment_number' => $payment['payment_number'],
            'customer_id' => $payment['customer_id'],
            'invoice_id' => $payment['invoice_id'],
            'amount' => $payment['amount'],
            'company_id' => $payment['company_id'],
        ]);
    }

    #[Test]
    public function it_creates_a_partial_payment_and_updates_the_invoice_paid_status(): void
    {
        // Arrange
        $invoice = Invoice::factory()->create([
            'sub_total' => 100,
            'total' => 100,
            'due_amount' => 100,
            'exchange_rate' => 1,
            'base_discount_val' => 100,
            'base_sub_total' => 100,
            'base_total' => 100,
            'base_tax' => 100,
            'base_due_amount' => 100,
        ]);
        $payment = Payment::factory()->raw([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'exchange_rate' => $invoice->exchange_rate,
            'amount' => 100,
            'currency_id' => $invoice->currency_id,
        ]);

        // Act
        $response = $this->postJson('api/v1/payments', $payment)->assertOk();

        // Assert
        $this->assertDatabaseHas('payments', [
            'payment_number' => $payment['payment_number'],
            'customer_id' => (string) $payment['customer_id'],
            'amount' => (string) $payment['amount'],
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice['id'],
            'invoice_number' => $response['data']['invoice']['invoice_number'],
            'total' => $response['data']['invoice']['total'],
            'customer_id' => $response['data']['invoice']['customer_id'],
            'exchange_rate' => $response['data']['invoice']['exchange_rate'],
            'base_total' => $response['data']['invoice']['base_total'],
            'paid_status' => $response['data']['invoice']['paid_status'],
        ]);
    }
}
