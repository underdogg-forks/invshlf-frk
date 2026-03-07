<?php

namespace Tests\Unit;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_payments(): void
    {
        // Arrange
        $method = PaymentMethod::factory()->hasPayments(5)->create();

        // Act & Assert
        $this->assertCount(5, $method->payments);
        $this->assertTrue($method->payments()->exists());
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        // Arrange
        $method = PaymentMethod::factory()->create();

        // Act & Assert
        $this->assertTrue($method->company()->exists());
    }
}
