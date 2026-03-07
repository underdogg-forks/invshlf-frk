<?php

namespace Tests\Feature\Admin;

use App\Models\FileDisk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileDiskTest extends TestCase
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
    public function it_retrieves_all_file_disks(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('/api/v1/disks');

        /* Assert */
        $response->assertOk();
    }

    #[Test]
    public function it_creates_a_file_disk(): void
    {
        /* Arrange */
        $disk = FileDisk::factory()->raw();

        /* Act */
        $this->postJson('/api/v1/disks', $disk);

        /* Assert */
        $disk['credentials'] = json_encode($disk['credentials']);
        $this->assertDatabaseHas('file_disks', $disk);
    }

    #[Test]
    public function it_updates_a_file_disk(): void
    {
        /* Arrange */
        $disk = FileDisk::factory()->create();
        $updatedDisk = FileDisk::factory()->raw();

        /* Act */
        $this->putJson("/api/v1/disks/{$disk->id}", $updatedDisk)->assertStatus(200);

        /* Assert */
        $updatedDisk['credentials'] = json_encode($updatedDisk['credentials']);
        $updatedDisk['id'] = $disk->id;
        $this->assertDatabaseHas('file_disks', $updatedDisk);
    }

    #[Test]
    public function it_retrieves_a_single_disk_by_driver(): void
    {
        /* Arrange */
        $disk = FileDisk::factory()->create();

        /* Act */
        $response = $this->getJson("/api/v1/disks/{$disk->driver}");

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_retrieves_available_disk_drivers(): void
    {
        /* Arrange */

        /* Act */
        $response = $this->getJson('/api/v1/disk/drivers');

        /* Assert */
        $response->assertStatus(200);
    }
}
