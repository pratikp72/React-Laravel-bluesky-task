<?php

namespace Tests\Feature\Api;

use App\Models\BlueskyAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlueskyAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_can_be_linked(): void
    {
        Http::fake([
            '*createSession' => Http::response([
                'did' => 'did:plc:unit123',
                'handle' => 'studio.test',
                'accessJwt' => 'access-token',
                'refreshJwt' => 'refresh-token',
            ]),
        ]);

        $response = $this->postJson('/api/v1/accounts', [
            'handle' => 'studio.test',
            'app_password' => 'ABCD-EFGH-IJKL-MNOP',
            'label' => 'Studio',
        ]);

        $response->assertCreated()->assertJsonPath('data.handle', 'studio.test');

        $this->assertDatabaseHas('bluesky_accounts', [
            'handle' => 'studio.test',
            'status' => BlueskyAccount::STATUS_CONNECTED,
        ]);
    }

    public function test_account_tokens_can_be_refreshed(): void
    {
        $account = BlueskyAccount::factory()->create([
            'refresh_jwt' => 'refresh-token',
        ]);

        Http::fake([
            '*refreshSession' => Http::response([
                'did' => $account->did,
                'handle' => $account->handle,
                'accessJwt' => 'new-access',
                'refreshJwt' => 'new-refresh',
            ]),
        ]);

        $this->postJson("/api/v1/accounts/{$account->id}/refresh")
            ->assertOk()
            ->assertJsonPath('data.id', $account->id);

        $this->assertSame(BlueskyAccount::STATUS_CONNECTED, $account->fresh()->status);
    }
}
