<?php

namespace Tests\Unit;

use App\Models\CustomFieldValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldValueTest extends TestCase
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
        // Arrange
        $fieldValue = CustomFieldValue::factory()->create();

        // Act & Assert
        $this->assertTrue($fieldValue->company()->exists());
    }

    #[Test]
    public function it_belongs_to_a_custom_field(): void
    {
        // Arrange
        $fieldValue = CustomFieldValue::factory()->forCustomField()->create();

        // Act & Assert
        $this->assertTrue($fieldValue->customField()->exists());
    }
}
