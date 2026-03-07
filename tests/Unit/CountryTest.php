<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_addresses(): void
    {
        // Arrange
        $country = Country::find(1);
        Address::factory()->count(5)->create(['country_id' => $country->id]);

        // Act & Assert
        $this->assertTrue($country->address()->exists());
    }
}
