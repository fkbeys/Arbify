<?php

namespace Tests\Feature;

use App\Language;
use App\Message;
use App\MessageValue;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testIndexShows(): void
    {
        [$project, $message] = $this->projectAndMessage();

        $this->actingAsUser()->get("/projects/$project->id/messages")
            ->assertOk()
            ->assertSeeText($message->name);
    }

    public function testCreateShows(): void
    {
        $project = factory(Project::class)->create();

        $this->actingAsUser()->get("/projects/$project->id/messages/create")
            ->assertOk()
            ->assertSeeText('Message name')
            ->assertSeeText('Create message');
    }

    public function testStoreWithCorrectData(): void
    {
        $project = factory(Project::class)->create();
        $messageData = factory(Message::class)->make();

        $this->actingAsUser()->post("/projects/$project->id/messages", [
            'name' => $messageData->name,
            'description' => $messageData->description,
            'type' => $messageData->type,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('messages', ['name' => $messageData->name, 'project_id' => $project->id]);
    }

    public function testEditShows(): void
    {
        [$project, $message] = $this->projectAndMessage();

        $this->actingAsUser()->get("/projects/$project->id/messages/$message->id/edit")
            ->assertOk()
            ->assertSee($message->name)
            ->assertSeeText('Update message');
    }

    public function testUpdateWithCorrectData(): void
    {
        [$project, $message] = $this->projectAndMessage();
        $newDescription = $this->faker()->sentences(3, true);

        $this->actingAsUser()->patch("/projects/$project->id/messages/$message->id", [
            'name' => $message->name,
            'description' => $newDescription,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('messages', ['id' => $message->id, 'description' => $newDescription]);
    }

    public function testDestroyWithCorrectData(): void
    {
        [$project, $message] = $this->projectAndMessage();

        $this->actingAsUser()->delete("/projects/$project->id/messages/$message->id")
            ->assertRedirect();

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function testPutMessageValueWithCorrectData(): void
    {
        [$project, $message] = $this->projectAndMessage();
        $language = factory(Language::class)->create();
        $project->languages()->attach($language);

        $value = $this->faker()->sentence();

        $this->actingAsUser()->put("/projects/$project->id/messages/$message->id/$language->code", [
            'value' => $value,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('message_values', [
            'message_id' => $message->id,
            'language_id' => $language->id,
            'value' => $value,
        ]);
    }

    private function projectAndMessage(): array
    {
        $project = factory(Project::class)->create();
        $message = factory(Message::class)->create();
        $message->project()->associate($project)->save();

        return [$project, $message];
    }
}
