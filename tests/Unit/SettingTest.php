<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_sets_and_retrieves_a_global_setting(): void
    {
        /* Arrange */
        $key = fake()->name();
        $value = fake()->word();

        /* Act */
        Setting::setSetting($key, $value);
        $result = Setting::getSetting($key);

        /* Assert */
        $this->assertEquals($value, $result);
    }
}
