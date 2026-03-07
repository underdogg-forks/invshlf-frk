<?php

namespace Tests\Unit;

use App\Models\ExchangeRateLog;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExchangeRateLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        /* Arrange */
        $exchangeRateLog = ExchangeRateLog::factory()->forCompany()->create();

        /* Act & Assert */
        $this->assertTrue($exchangeRateLog->company->exists());
    }

    #[Test]
    public function it_adds_an_exchange_rate_log_from_an_expense(): void
    {
        /* Arrange */
        $expense = Expense::factory()->create();

        /* Act */
        $response = ExchangeRateLog::addExchangeRateLog($expense);

        /* Assert */
        $this->assertDatabaseHas('exchange_Rate_logs', [
            'exchange_rate' => $response->exchange_rate,
            'base_currency_id' => $response->base_currency_id,
            'currency_id' => $response->currency_id,
        ]);
    }
}
