<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BlueskyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlueskyAccountRequest;
use App\Models\BlueskyAccount;
use App\Services\BlueskyClient;
use Illuminate\Http\JsonResponse;

class BlueskyAccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = BlueskyAccount::query()
            ->latest()
            ->get()
            ->map(fn (BlueskyAccount $account) => $this->present($account));

        return response()->json(['data' => $accounts]);
    }

    public function store(StoreBlueskyAccountRequest $request, BlueskyClient $client): JsonResponse
    {
        $payload = $request->validated();

        try {
            $session = $client->createSession($payload['handle'], $payload['app_password']);
        } catch (BlueskyException $exception) {
            return $this->blueskyFailure($exception);
        }

        $account = BlueskyAccount::updateOrCreate(
            ['handle' => $payload['handle']],
            array_filter([
                'label' => $payload['label'] ?? null,
                'service' => config('services.bluesky.base_url'),
                'status' => BlueskyAccount::STATUS_CONNECTED,
                'app_password' => $payload['app_password'],
                'did' => $session['did'] ?? null,
                'access_jwt' => $session['accessJwt'] ?? null,
                'refresh_jwt' => $session['refreshJwt'] ?? null,
                'last_authenticated_at' => now(),
                'meta' => [
                    'handle' => $session['handle'] ?? $payload['handle'],
                    'did' => $session['did'] ?? null,
                ],
            ], static fn ($value) => $value !== null),
        );

        return response()->json(['data' => $this->present($account->fresh())], 201);
    }

    public function refresh(BlueskyAccount $account, BlueskyClient $client): JsonResponse
    {
        try {
            $session = $account->refresh_jwt
                ? $client->refreshSession($account->refresh_jwt)
                : $client->createSession($account->handle, $account->app_password);
        } catch (BlueskyException $exception) {
            return $this->blueskyFailure($exception);
        }

        $account->markConnected($session);

        return response()->json(['data' => $this->present($account->fresh())]);
    }

    public function destroy(BlueskyAccount $account): JsonResponse
    {
        $account->delete();

        return response()->json(status: 204);
    }

    protected function blueskyFailure(BlueskyException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'context' => $exception->context(),
        ], 422);
    }

    protected function present(BlueskyAccount $account): array
    {
        return [
            'id' => $account->id,
            'label' => $account->label,
            'handle' => $account->handle,
            'status' => $account->status,
            'service' => $account->service,
            'did' => $account->did,
            'last_authenticated_at' => optional($account->last_authenticated_at)->toIso8601String(),
            'created_at' => optional($account->created_at)->toIso8601String(),
            'updated_at' => optional($account->updated_at)->toIso8601String(),
        ];
    }
}
