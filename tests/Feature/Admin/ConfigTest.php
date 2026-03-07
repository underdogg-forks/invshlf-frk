<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfigTest extends TestCase
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
    public function it_retrieves_config_values_for_supported_keys(): void
    {
        // Arrange
        $supportedKeys = [
            'languages',
            'fiscal_years',
            'convert_estimate_options',
            'retrospective_edits',
            'currency_converter_servers',
            'exchange_rate_drivers',
            'custom_field_models',
        ];

        // Act & Assert
        foreach ($supportedKeys as $key) {
            $this->getJson('api/v1/config?key='.$key)->assertOk();
        }
    }
}
