<?php

namespace Tests\Feature\Api;

use App\Jobs\PublishScheduledPost;
use App\Models\BlueskyAccount;
use App\Models\ScheduledPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScheduledPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_can_be_created(): void
    {
        Queue::fake();

        $account = BlueskyAccount::factory()->create();

        $response = $this->postJson('/api/v1/schedules', [
            'account_id' => $account->id,
            'content' => 'Shipping the Bluesky integration from our scheduler.',
            'publish_at' => now()->addMinutes(30)->toIso8601String(),
        ]);

        $response->assertCreated();

        $scheduleId = $response->json('data.id');

        Queue::assertPushed(PublishScheduledPost::class, function (PublishScheduledPost $job) use ($scheduleId) {
            return $job->scheduledPostId === $scheduleId;
        });
    }

    public function test_schedule_can_be_sent_immediately(): void
    {
        Queue::fake();

        $schedule = ScheduledPost::factory()->create([
            'publish_at' => now()->subMinute(),
        ]);

        $this->postJson("/api/v1/schedules/{$schedule->id}/send")
            ->assertOk();

        Queue::assertPushed(PublishScheduledPost::class, function (PublishScheduledPost $job) use ($schedule) {
            return $job->scheduledPostId === $schedule->id;
        });
    }
}
