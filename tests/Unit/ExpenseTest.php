<?php

namespace Tests\Unit;

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_a_category(): void
    {
        /* Arrange */
        $expense = Expense::factory()->forCategory()->create();

        /* Act & Assert */
        $this->assertTrue($expense->category()->exists());
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        /* Arrange */
        $expense = Expense::factory()->forCustomer()->create();

        /* Act & Assert */
        $this->assertTrue($expense->customer()->exists());
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        /* Arrange */
        $expense = Expense::factory()->forCompany()->create();

        /* Act & Assert */
        $this->assertTrue($expense->company()->exists());
    }
}
