<?php

namespace Database\Factories;

use App\Models\BlueskyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlueskyAccount>
 */
class BlueskyAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $handle = strtolower($this->faker->unique()->userName()).'.bsky.social';

        return [
            'label' => ucfirst($this->faker->word()),
            'handle' => $handle,
            'service' => 'https://bsky.social',
            'status' => BlueskyAccount::STATUS_CONNECTED,
            'app_password' => sprintf('%s-%s-%s-%s', Str::upper(Str::random(4)), Str::upper(Str::random(4)), Str::upper(Str::random(4)), Str::upper(Str::random(4))),
            'did' => 'did:plc:'.$this->faker->unique()->bothify('#########'),
            'access_jwt' => Str::random(64),
            'refresh_jwt' => Str::random(64),
            'last_authenticated_at' => now(),
            'meta' => ['handle' => $handle],
        ];
    }
}
