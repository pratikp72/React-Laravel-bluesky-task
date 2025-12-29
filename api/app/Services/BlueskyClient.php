<?php

namespace App\Services;

use App\Exceptions\BlueskyException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BlueskyClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeoutSeconds = 10,
    ) {
    }

    public function createSession(string $identifier, string $appPassword): array
    {
        $response = $this->httpJson()->post('/xrpc/com.atproto.server.createSession', [
            'identifier' => $identifier,
            'password' => $appPassword,
        ]);

        return $this->parseResponse($response, 'Unable to establish a Bluesky session.');
    }

    public function refreshSession(string $refreshJwt): array
    {
        // Bluesky expects an empty-body POST here; using send() avoids Laravel
        // adding an empty JSON payload that the API rejects.
        $response = $this->httpBase()
            ->withToken($refreshJwt)
            ->withHeaders(['Content-Length' => 0])
            ->send('POST', '/xrpc/com.atproto.server.refreshSession');

        return $this->parseResponse($response, 'Unable to refresh the Bluesky session.');
    }

    public function createPost(string $accessJwt, string $did, string $text): array
    {
        $response = $this->httpJson()
            ->withToken($accessJwt)
            ->post('/xrpc/com.atproto.repo.createRecord', [
                'repo' => $did,
                'collection' => 'app.bsky.feed.post',
                'record' => [
                    '$type' => 'app.bsky.feed.post',
                    'text' => $text,
                    'createdAt' => now()->toISOString(),
                ],
            ]);

        return $this->parseResponse($response, 'Unable to publish the post to Bluesky.');
    }

    protected function httpJson(): PendingRequest
    {
        return $this->httpBase()->asJson();
    }

    protected function httpBase(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withHeaders(['User-Agent' => 'BlueskyScheduler/1.0'])
            ->retry(2, 250)
            ->timeout($this->timeoutSeconds);
    }

    protected function parseResponse(Response $response, string $message): array
    {
        if ($response->failed()) {
            $body = $response->json() ?? [];
            $detail = $body['message'] ?? $body['error'] ?? null;
            $reason = implode(' - ', array_filter([$message, $detail]));

            throw new BlueskyException($reason, response: $response);
        }

        return $response->json();
    }
}
