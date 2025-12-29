<?php

namespace Database\Factories;

use App\Models\BlueskyAccount;
use App\Models\ScheduledPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduledPost>
 */
class ScheduledPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bluesky_account_id' => BlueskyAccount::factory(),
            'content' => $this->faker->realText(180),
            'publish_at' => now()->addMinutes(random_int(5, 120)),
            'status' => ScheduledPost::STATUS_SCHEDULED,
            'queued_at' => null,
        ];
    }
}
