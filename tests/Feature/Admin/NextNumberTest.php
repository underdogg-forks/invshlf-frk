<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NextNumberTest extends TestCase
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
    public function it_returns_the_next_sequential_number_for_each_document_type(): void
    {
        /* Arrange */
        $documentTypes = [
            'invoice' => 'INV-000001',
            'estimate' => 'EST-000001',
            'payment' => 'PAY-000001',
        ];

        /* Act & Assert */
        foreach ($documentTypes as $key => $expectedNumber) {
            $this->getJson('api/v1/next-number?key='.$key)
                ->assertStatus(200)
                ->assertJson(['nextNumber' => $expectedNumber]);
        }
    }
}
