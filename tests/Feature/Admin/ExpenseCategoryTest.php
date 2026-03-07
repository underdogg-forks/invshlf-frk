<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Expense\ExpenseCategoriesController;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
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
    public function it_retrieves_all_expense_categories(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/categories');

        /* Assert */
        $response->assertOk()
            ->assertJsonStructure(['data']);
    {
        /* Arrange */
        $category = ExpenseCategory::factory()->raw();

        /* Act */
        $response = $this->postJson('api/v1/categories', $category);

        /* Assert */
        $response->assertStatus(201);
        $this->assertDatabaseHas('expense_categories', [
            'name' => $category['name'],
            'description' => $category['description'],
        ]);
    }

    #[Test]
    public function it_validates_the_store_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            ExpenseCategoriesController::class,
            'store',
            ExpenseCategoryRequest::class
        );
    }

    #[Test]
    public function it_retrieves_a_single_expense_category(): void
    {
        /* Arrange */
        $category = ExpenseCategory::factory()->create();

        /* Act & Assert */
        $this->getJson("api/v1/categories/{$category->id}")
            ->assertOk()
            ->assertJson(['data' => ['id' => $category->id, 'name' => $category->name]]);
    }

    #[Test]
    public function it_updates_an_expense_category(): void
    {
        /* Arrange */
        $category = ExpenseCategory::factory()->create();
        $updatedData = ExpenseCategory::factory()->raw();

        /* Act */
        $this->putJson('api/v1/categories/'.$category->id, $updatedData)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
        ]);
    }

    #[Test]
    public function it_validates_the_update_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            ExpenseCategoriesController::class,
            'update',
            ExpenseCategoryRequest::class
        );
    }

    #[Test]
    public function it_deletes_an_expense_category(): void
    {
        /* Arrange */
        $category = ExpenseCategory::factory()->create();

        /* Act & Assert */
        $this->deleteJson('api/v1/categories/'.$category->id)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertModelMissing($category);
    }
}
