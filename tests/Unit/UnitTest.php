<?php

namespace Tests\Unit;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

        $user = User::where('role', 'super admin')->first();
        $this->withHeaders(['company' => $user->companies()->first()->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_has_many_items(): void
    {
        /* Arrange */
        $unit = Unit::factory()->hasItems(5)->create();

        /* Act & Assert */
        $this->assertTrue($unit->items()->exists());
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        /* Arrange */
        $unit = Unit::factory()->create();

        /* Act & Assert */
        $this->assertTrue($unit->company()->exists());
    }
}
