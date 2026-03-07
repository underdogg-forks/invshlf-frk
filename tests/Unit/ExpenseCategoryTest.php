<?php

namespace Tests\Unit;

use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
    }

    #[Test]
    public function it_has_many_expenses(): void
    {
        // Arrange
        $category = ExpenseCategory::factory()->hasExpenses(5)->create();

        // Act & Assert
        $this->assertCount(5, $category->expenses);
        $this->assertTrue($category->expenses()->exists());
    }
}
