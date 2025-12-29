<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishNowRequest;
use App\Http\Requests\StoreScheduledPostRequest;
use App\Jobs\PublishScheduledPost;
use App\Models\ScheduledPost;
use Illuminate\Http\JsonResponse;

class ScheduledPostController extends Controller
{
    public function index(): JsonResponse
    {
        $schedules = ScheduledPost::with('account:id,label,handle,status')
            ->latest('publish_at')
            ->get()
            ->map(fn (ScheduledPost $post) => $this->present($post));

        return response()->json(['data' => $schedules]);
    }

    public function store(StoreScheduledPostRequest $request): JsonResponse
    {
        $schedule = ScheduledPost::create([
            'bluesky_account_id' => $request->integer('account_id'),
            'content' => $request->string('content'),
            'publish_at' => $request->date('publish_at'),
            'status' => ScheduledPost::STATUS_SCHEDULED,
        ])->load('account');

        $schedule->markQueued();

        PublishScheduledPost::dispatch($schedule->id)
            ->delay($schedule->publish_at);

        return response()->json(['data' => $this->present($schedule)], 201);
    }

    public function publishNow(PublishNowRequest $request): JsonResponse
    {
        $schedule = ScheduledPost::create([
            'bluesky_account_id' => $request->integer('account_id'),
            'content' => $request->string('content'),
            'publish_at' => now(),
            'status' => ScheduledPost::STATUS_SCHEDULED,
        ])->load('account');

        $schedule->markQueued();

        PublishScheduledPost::dispatch($schedule->id);

        return response()->json(['data' => $this->present($schedule)], 201);
    }

    public function sendNow(ScheduledPost $scheduledPost): JsonResponse
    {
        if ($scheduledPost->isTerminal()) {
            return response()->json(['message' => 'Post has already been processed.'], 422);
        }

        $scheduledPost->markQueued();
        PublishScheduledPost::dispatch($scheduledPost->id);

        return response()->json(['data' => $this->present($scheduledPost->fresh('account'))]);
    }

    public function cancel(ScheduledPost $scheduledPost): JsonResponse
    {
        if ($scheduledPost->isTerminal()) {
            return response()->json(['message' => 'Post has already been processed.'], 422);
        }

        $scheduledPost->cancel();

        return response()->json(['data' => $this->present($scheduledPost)]);
    }

    protected function present(ScheduledPost $post): array
    {
        return [
            'id' => $post->id,
            'content' => $post->content,
            'status' => $post->status,
            'publish_at' => optional($post->publish_at)->toIso8601String(),
            'queued_at' => optional($post->queued_at)->toIso8601String(),
            'remote_uri' => $post->remote_uri,
            'failure_reason' => $post->failure_reason,
            'account' => $post->relationLoaded('account') ? [
                'id' => $post->account->id,
                'label' => $post->account->label,
                'handle' => $post->account->handle,
            ] : null,
        ];
    }
}
