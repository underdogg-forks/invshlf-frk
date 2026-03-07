<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanySettingTest extends TestCase
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
        $setting = CompanySetting::factory()->create();

        /* Act & Assert */
        $this->assertTrue($setting->company()->exists());
    }

    #[Test]
    public function it_sets_and_retrieves_a_single_setting(): void
    {
        /* Arrange */
        $key = fake()->name();
        $value = fake()->word();
        $company = Company::factory()->create();

        /* Act */
        CompanySetting::setSettings([$key => $value], $company->id);
        $result = CompanySetting::getSetting($key, $company->id);

        /* Assert */
        $this->assertEquals($value, $result);
    }

    #[Test]
    public function it_sets_and_retrieves_multiple_settings(): void
    {
        /* Arrange */
        $key = fake()->name();
        $value = fake()->word();
        $company = Company::factory()->create();

        /* Act */
        CompanySetting::setSettings([$key => $value], $company->id);
        $result = CompanySetting::getSettings([$key], $company->id);

        /* Assert */
        $this->assertEquals([$key => $value], $result->toArray());
    }
}
