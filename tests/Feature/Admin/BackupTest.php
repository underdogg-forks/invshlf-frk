<?php

namespace Tests\Feature\Admin;

use App\Jobs\CreateBackupJob;
use App\Models\FileDisk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackupTest extends TestCase
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
    public function it_retrieves_backups_for_a_given_disk(): void
    {
        /* Arrange */
        $disk = FileDisk::factory()->create(['set_as_default' => true]);

        /* Act */
        $response = $this->getJson("/api/v1/backups?disk={$disk->driver}&file_disk_id={$disk->id}");

        /* Assert */
        $response->assertOk()
            ->assertJsonStructure(['backups', 'disks']);
    {
        /* Arrange */
        Queue::fake();
        $disk = FileDisk::factory()->create();
        $data = [
            'option' => 'full',
            'file_disk_id' => $disk->id,
        ];

        /* Act */
        $this->postJson('/api/v1/backups', $data)->assertOk();

        /* Assert */
        Queue::assertPushed(CreateBackupJob::class);

        $this->getJson("/api/v1/backups?disk={$disk->driver}&&file_disk_id={$disk->id}")
            ->assertStatus(200)
            ->assertJson(['disks' => ['local']]);
    }
}
