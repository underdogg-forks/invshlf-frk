<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_belongs_to_a_currency(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->assertTrue($user->currency()->exists());
    }

    #[Test]
    public function it_belongs_to_many_companies(): void
    {
        // Arrange
        $user = User::factory()->hasCompanies(5)->create();

        // Act & Assert
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->companies);
    }
}
