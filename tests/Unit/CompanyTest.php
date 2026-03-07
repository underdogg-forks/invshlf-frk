<?php

namespace Tests\Unit;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
    }

    #[Test]
    public function it_has_many_customers(): void
    {
        /* Arrange */
        $company = Company::factory()->hasCustomers()->create();

        /* Act & Assert */
        $this->assertTrue($company->customers()->exists());
    }

    #[Test]
    public function it_has_many_company_settings(): void
    {
        /* Arrange */
        $company = Company::factory()->hasSettings(5)->create();

        /* Act & Assert */
        $this->assertCount(5, $company->settings);
        $this->assertTrue($company->settings()->exists());
    }

    #[Test]
    public function it_belongs_to_many_users(): void
    {
        /* Arrange */
        $company = Company::factory()->hasUsers(5)->create();

        /* Act & Assert */
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $company->users);
    }
}
