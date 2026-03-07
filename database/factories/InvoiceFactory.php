<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use App\Models\User;
use App\Services\SerialNumberFormatter;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => InvoiceStatus::Sent,
            ];
        });
    }

    public function viewed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => InvoiceStatus::Viewed,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => InvoiceStatus::Completed,
            ];
        });
    }

    public function unpaid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => InvoiceStatus::Unpaid,
            ];
        });
    }

    public function partiallyPaid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => InvoiceStatus::PartiallyPaid,
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => InvoiceStatus::Paid,
            ];
        });
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $sequenceNumber = (new SerialNumberFormatter)
            ->setModel(new Invoice)
            ->setCompany(User::find(1)->companies()->first()->id)
            ->setNextNumbers();

        return [
            'invoice_date' => $this->faker->date('Y-m-d', 'now'),
            'due_date' => $this->faker->date('Y-m-d', 'now'),
            'invoice_number' => $sequenceNumber->getNextNumber(),
            'sequence_number' => $sequenceNumber->nextSequenceNumber,
            'customer_sequence_number' => $sequenceNumber->nextCustomerSequenceNumber,
            'reference_number' => $sequenceNumber->getNextNumber(),
            'template_name' => 'invoice1',
            'status' => InvoiceStatus::Draft,
            'tax_per_item' => 'NO',
            'tax_included' => false,
            'discount_per_item' => 'NO',
            'paid_status' => InvoiceStatus::Unpaid,
            'company_id' => User::find(1)->companies()->first()->id,
            'sub_total' => $this->faker->randomDigitNotNull(),
            'total' => $this->faker->randomDigitNotNull(),
            'discount_type' => $this->faker->randomElement(['percentage', 'fixed']),
            'discount_val' => function (array $invoice) {
                return $invoice['discount_type'] == 'percentage' ? $this->faker->numberBetween($min = 0, $max = 100) : $this->faker->randomDigitNotNull();
            },
            'discount' => function (array $invoice) {
                return $invoice['discount_type'] == 'percentage' ? (($invoice['discount_val'] * $invoice['total']) / 100) : $invoice['discount_val'];
            },
            'tax' => $this->faker->randomDigitNotNull(),
            'due_amount' => function (array $invoice) {
                return $invoice['total'];
            },
            'notes' => $this->faker->text(80),
            'unique_hash' => str_random(60),
            'customer_id' => Customer::factory(),
            'recurring_invoice_id' => RecurringInvoice::factory(),
            'exchange_rate' => $this->faker->randomDigitNotNull(),
            'base_discount_val' => $this->faker->randomDigitNotNull(),
            'base_sub_total' => $this->faker->randomDigitNotNull(),
            'base_total' => $this->faker->randomDigitNotNull(),
            'base_tax' => $this->faker->randomDigitNotNull(),
            'base_due_amount' => $this->faker->randomDigitNotNull(),
            'currency_id' => Currency::find(1)->id,
        ];
    }
}
