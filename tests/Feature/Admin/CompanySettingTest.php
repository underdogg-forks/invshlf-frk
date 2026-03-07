<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\V1\Admin\Settings\CompanyController;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
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

        $user = User::find(1);
        $this->withHeaders(['company' => $user->companies()->first()->id]);
        Sanctum::actingAs($user, ['*']);
    }

    #[Test]
    public function it_retrieves_the_current_user_profile(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('api/v1/me');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_validates_the_update_profile_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            CompanyController::class,
            'updateProfile',
            ProfileRequest::class
        );
    }

    #[Test]
    public function it_updates_the_user_profile(): void
    {
        /* Arrange */
        $user = [
            'name' => 'John Doe',
            'password' => 'admin@123',
            'email' => 'admin@invoiceshelf.com',
        ];

        /* Act */
        $response = $this->putJson('api/v1/me', $user);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }

    #[Test]
    public function it_validates_the_update_company_action_uses_a_form_request(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->assertActionUsesFormRequest(
            CompanyController::class,
            'updateCompany',
            CompanyRequest::class
        );
    }

    #[Test]
    public function it_updates_the_company_details(): void
    {
        /* Arrange */
        $company = [
            'name' => 'XYZ',
            'country_id' => 2,
            'state' => 'city',
            'city' => 'state',
            'address_street_1' => 'test1',
            'address_street_2' => 'test2',
            'phone' => '1234567890',
            'zip' => '112233',
            'address' => ['country_id' => 2],
        ];

        /* Act */
        $this->putJson('api/v1/company', $company)->assertOk();

        /* Assert */
        $this->assertDatabaseHas('companies', ['name' => $company['name']]);
        $this->assertDatabaseHas('addresses', ['country_id' => $company['country_id']]);
    }

    #[Test]
    public function it_updates_company_settings(): void
    {
        /* Arrange */
        $settings = [
            'currency' => 1,
            'time_zone' => 'Asia/Kolkata',
            'language' => 'en',
            'fiscal_year' => '1-12',
            'carbon_date_format' => 'Y/m/d',
            'moment_date_format' => 'YYYY/MM/DD',
            'notification_email' => 'noreply@invoiceshelf.com',
            'notify_invoice_viewed' => 'YES',
            'notify_estimate_viewed' => 'YES',
            'tax_per_item' => 'YES',
            'tax_included' => 'YES',
            'tax_included_by_default' => 'YES',
            'discount_per_item' => 'YES',
        ];

        /* Act */
        $response = $this->postJson('/api/v1/company/settings', ['settings' => $settings]);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        foreach ($settings as $key => $value) {
            $this->assertDatabaseHas('company_settings', ['option' => $key, 'value' => $value]);
        }
    }

    #[Test]
    public function it_updates_company_settings_without_a_currency(): void
    {
        /* Arrange */
        $settings = ['notification_email' => 'noreply@invoiceshelf.com'];

        /* Act */
        $response = $this->postJson('/api/v1/company/settings', ['settings' => $settings]);

        /* Assert */
        $response->assertOk()->assertJson(['success' => true]);
        foreach ($settings as $key => $value) {
            $this->assertDatabaseHas('company_settings', ['option' => $key, 'value' => $value]);
        }
    }

    #[Test]
    public function it_prevents_updating_currency_when_transactions_exist(): void
    {
        /* Arrange */
        $this->postJson('/api/v1/company/settings', ['settings' => ['currency' => 1]])
            ->assertOk()
            ->assertJson(['success' => true]);

        Invoice::factory()->create([
            'taxes' => [Tax::factory()->raw()],
            'items' => [InvoiceItem::factory()->raw()],
        ]);

        /* Act */
        $response = $this->postJson('/api/v1/company/settings', ['settings' => ['currency' => 2]]);

        /* Assert */
        $response->assertOk()->assertJson([
            'success' => false,
            'message' => 'Cannot update company currency after transactions are created.',
        ]);
        $this->assertDatabaseHas('company_settings', ['option' => 'currency', 'value' => 1]);
    }

    #[Test]
    public function it_retrieves_company_notification_settings(): void
    {
        /* Arrange */
        $settingKeys = [
            'currency', 'time_zone', 'language', 'fiscal_year',
            'carbon_date_format', 'moment_date_format', 'notification_email',
            'notify_invoice_viewed', 'notify_estimate_viewed',
            'tax_per_item', 'discount_per_item',
        ];

        /* Act */
        $response = $this->getJson('/api/v1/company/settings?'.http_build_query(['settings' => $settingKeys]));

        /* Assert */
        $response->assertOk();
    }
}
