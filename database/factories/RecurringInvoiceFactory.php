<?php

namespace Database\Factories;

use App\Enums\RecurringInvoiceLimitBy;
use App\Enums\RecurringInvoiceStatus;
use App\Models\Customer;
use App\Models\RecurringInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringInvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecurringInvoice::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'starts_at' => $this->faker->iso8601(),
            'send_automatically' => false,
            'status' => $this->faker->randomElement([RecurringInvoiceStatus::Completed->value, RecurringInvoiceStatus::OnHold->value, RecurringInvoiceStatus::Active->value]),
            'tax_per_item' => 'NO',
            'tax_included' => false,
            'discount_per_item' => 'NO',
            'sub_total' => $this->faker->randomDigitNotNull(),
            'total' => $this->faker->randomDigitNotNull(),
            'tax' => $this->faker->randomDigitNotNull(),
            'due_amount' => $this->faker->randomDigitNotNull(),
            'discount' => $this->faker->randomDigitNotNull(),
            'discount_val' => $this->faker->randomDigitNotNull(),
            'customer_id' => Customer::factory(),
            'company_id' => User::find(1)->companies()->first()->id,
            'frequency' => '* * 18 * *',
            'limit_by' => $this->faker->randomElement([RecurringInvoiceLimitBy::None->value, RecurringInvoiceLimitBy::Count->value, RecurringInvoiceLimitBy::Date->value]),
            'limit_count' => $this->faker->randomDigit(),
            'limit_date' => $this->faker->date(),
            'exchange_rate' => $this->faker->randomDigitNotNull(),
        ];
    }
}
