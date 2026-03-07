<?php

namespace Tests\Unit;

use App\Models\CustomField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomFieldTest extends TestCase
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
        $customField = CustomField::factory()->create();

        /* Act & Assert */
        $this->assertTrue($customField->company()->exists());
    }

    #[Test]
    public function it_has_many_custom_field_values(): void
    {
        /* Arrange */
        $customField = CustomField::factory()->hasCustomFieldValues(5)->create();

        /* Act & Assert */
        $this->assertCount(5, $customField->customFieldValues);
        $this->assertTrue($customField->customFieldValues()->exists());
    }
}
