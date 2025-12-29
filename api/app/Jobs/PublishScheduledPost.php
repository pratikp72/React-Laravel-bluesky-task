<?php

namespace App\Jobs;

use App\Exceptions\BlueskyException;
use App\Models\BlueskyAccount;
use App\Models\ScheduledPost;
use App\Services\BlueskyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 20;

    public function __construct(public readonly int $scheduledPostId)
    {
        $this->onQueue('scheduled-posts');
    }

    public function handle(BlueskyClient $client): void
    {
        Log::info('PublishScheduledPost started', [
            'scheduled_post_id' => $this->scheduledPostId,
        ]);

        $post = ScheduledPost::with('account')->find($this->scheduledPostId);

        if (! $post || $post->isTerminal()) {
            Log::warning('PublishScheduledPost exiting because post is missing or terminal', [
                'scheduled_post_id' => $this->scheduledPostId,
                'found_post' => (bool) $post,
                'status' => $post?->status,
            ]);
            return;
        }

        if ($post->publish_at?->isFuture()) {
            $delaySeconds = $post->publish_at->diffInSeconds(now()) + 5;
            Log::info('PublishScheduledPost releasing job until publish_at', [
                'scheduled_post_id' => $this->scheduledPostId,
                'publish_at' => $post->publish_at->toIso8601String(),
                'release_in_seconds' => $delaySeconds,
            ]);
            $this->release($post->publish_at->diffInSeconds(now()) + 5);
            return;
        }

        $account = $post->account;

        if (! $account instanceof BlueskyAccount) {
            $post->markFailed('Linked Bluesky account is missing.');
            Log::error('PublishScheduledPost cannot post because account missing', [
                'scheduled_post_id' => $this->scheduledPostId,
            ]);
            return;
        }

        try {
            $session = $account->refresh_jwt
                ? $client->refreshSession($account->refresh_jwt)
                : $client->createSession($account->handle, $account->app_password);

            $account->markConnected($session);
            Log::info('PublishScheduledPost authenticated Bluesky account', [
                'scheduled_post_id' => $this->scheduledPostId,
                'account_id' => $account->id,
                'has_refresh_jwt' => (bool) $account->refresh_jwt,
            ]);

            $response = $client->createPost(
                accessJwt: $account->access_jwt ?? $session['accessJwt'],
                did: $account->did ?? $session['did'],
                text: $post->content,
            );

            $post->markSent($response['uri'] ?? null);
            Log::info('PublishScheduledPost marked post as sent', [
                'scheduled_post_id' => $this->scheduledPostId,
                'remote_uri' => $response['uri'] ?? null,
            ]);
        } catch (BlueskyException $exception) {
            $context = $exception->context();
            $post->markFailed($exception->getMessage());
            Log::error('PublishScheduledPost failed while calling Bluesky', [
                'scheduled_post_id' => $this->scheduledPostId,
                'message' => $exception->getMessage(),
                'status' => $context['status'] ?? null,
                'response_body' => $context['body'] ?? null,
            ]);
            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning('PublishScheduledPost failed', [
            'scheduled_post_id' => $this->scheduledPostId,
            'message' => $exception->getMessage(),
        ]);
    }
}
