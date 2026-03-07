<?php

namespace Tests\Feature\Admin;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotesTest extends TestCase
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
    public function it_retrieves_all_notes(): void
    {
        /* Arrange */

        /* Act & Assert */
        $this->getJson('/api/v1/notes')->assertStatus(200);
    }

    #[Test]
    public function it_creates_a_note(): void
    {
        /* Arrange */
        $note = Note::factory()->raw();

        /* Act */
        $this->postJson('/api/v1/notes', $note)->assertStatus(201);

        /* Assert */
        $this->assertDatabaseHas('notes', $note);
    }

    #[Test]
    public function it_retrieves_a_single_note(): void
    {
        /* Arrange */
        $note = Note::factory()->create();

        /* Act & Assert */
        $this->getJson("/api/v1/notes/{$note->id}")->assertStatus(200);
    }

    #[Test]
    public function it_updates_a_note(): void
    {
        /* Arrange */
        $note = Note::factory()->create();
        $updatedData = Note::factory()->raw();

        /* Act */
        $this->putJson("/api/v1/notes/{$note->id}", $updatedData)->assertStatus(200);

        /* Assert */
        $this->assertDatabaseHas('notes', array_merge(
            ['id' => $note->id],
            $updatedData
        ));
    }

    #[Test]
    public function it_deletes_a_note(): void
    {
        /* Arrange */
        $note = Note::factory()->create();

        /* Act & Assert */
        $this->deleteJson("/api/v1/notes/{$note->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertModelMissing($note);
    }
}
