<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Expense\ExpensesController;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
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

        $user = User::find(1);
        $this->withHeaders(['company' => $user->companies()->first()->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_retrieves_a_paginated_list_of_expenses(): void
    {
        // Arrange - data seeded in setUp

        // Act
        $response = $this->getJson('api/v1/expenses?page=1');

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_creates_an_expense(): void
    {
        // Arrange
        $expense = Expense::factory()->raw([
            'amount' => 150,
            'exchange_rate' => 76.217498,
            'base_amount' => 11432.6247,
        ]);

        // Act
        $this->postJson('api/v1/expenses', $expense)->assertStatus(201);

        // Assert
        $this->assertDatabaseHas('expenses', [
            'notes' => $expense['notes'],
            'expense_category_id' => $expense['expense_category_id'],
            'amount' => $expense['amount'],
            'exchange_rate' => $expense['exchange_rate'],
            'base_amount' => $expense['base_amount'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            ExpensesController::class,
            'store',
            ExpenseRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_expense(): void
    {
        // Arrange
        $expense = Expense::factory()->create([
            'expense_number' => 'EXP-000001',
            'expense_date' => '2019-02-05',
        ]);

        // Act
        $response = $this->getJson("api/v1/expenses/{$expense->id}");

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_number' => $expense['expense_number'],
            'notes' => $expense['notes'],
            'expense_category_id' => $expense['expense_category_id'],
            'amount' => $expense['amount'],
        ]);
    }

    #[Test]
    public function it_updates_an_expense(): void
    {
        // Arrange
        $expense = Expense::factory()->create(['expense_date' => '2019-02-05']);
        $updatedExpense = Expense::factory()->raw();

        // Act
        $this->putJson('api/v1/expenses/'.$expense->id, $updatedExpense)->assertOk();

        // Assert
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'notes' => $updatedExpense['notes'],
            'expense_category_id' => $updatedExpense['expense_category_id'],
            'amount' => $updatedExpense['amount'],
        ]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        // Arrange - no setup required

        // Act & Assert
        $this->assertActionUsesFormRequest(
            ExpensesController::class,
            'update',
            ExpenseRequest::class
        );
    }

    #[Test]
    public function it_searches_expenses_by_filters(): void
    {
        // Arrange
        $filters = [
            'page' => 1,
            'limit' => 15,
            'expense_category_id' => 1,
            'search' => 'cate',
            'from_date' => '2020-07-18',
            'to_date' => '2020-07-20',
        ];

        // Act
        $response = $this->getJson('api/v1/expenses?'.http_build_query($filters, '', '&'));

        // Assert
        $response->assertOk();
    }

    #[Test]
    public function it_deletes_multiple_expenses(): void
    {
        // Arrange
        $expenses = Expense::factory()->count(3)->create(['expense_date' => '2019-02-05']);
        $data = ['ids' => $expenses->pluck('id')];

        // Act
        $response = $this->postJson('api/v1/expenses/delete', $data);

        // Assert
        $response->assertOk()->assertJson(['success' => true]);
        foreach ($expenses as $expense) {
            $this->assertModelMissing($expense);
        }
    }

    #[Test]
    public function it_updates_an_expense_with_foreign_currency(): void
    {
        // Arrange
        $expense = Expense::factory()->create(['expense_date' => '2019-02-05']);
        $updatedExpense = Expense::factory()->raw([
            'amount' => 150,
            'exchange_rate' => 76.217498,
            'base_amount' => 11432.6247,
        ]);

        // Act
        $this->putJson('api/v1/expenses/'.$expense->id, $updatedExpense)->assertOk();

        // Assert
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_category_id' => $updatedExpense['expense_category_id'],
            'amount' => $updatedExpense['amount'],
            'exchange_rate' => $updatedExpense['exchange_rate'],
            'base_amount' => $updatedExpense['base_amount'],
        ]);
    }
}
