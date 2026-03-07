<?php

namespace Tests\Unit;

use App\Models\TaxType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaxTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_taxes(): void
    {
        /* Arrange */
        $taxtype = TaxType::factory()->hasTaxes(4)->create();

        /* Act & Assert */
        $this->assertCount(4, $taxtype->taxes);
        $this->assertTrue($taxtype->taxes()->exists());
    }
}
