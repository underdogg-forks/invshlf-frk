<?php

namespace Tests\Unit;

use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        /* Arrange */
        $address = Address::factory()->forUser()->create();

        /* Act & Assert */
        $this->assertTrue($address->user->exists());
    }

    #[Test]
    public function it_belongs_to_a_country(): void
    {
        /* Arrange */
        $address = Address::factory()->create();

        /* Act & Assert */
        $this->assertTrue($address->country->exists());
    }

    #[Test]
    public function it_belongs_to_a_customer(): void
    {
        /* Arrange */
        $address = Address::factory()->forCustomer()->create();

        /* Act & Assert */
        $this->assertTrue($address->customer()->exists());
    }
}
