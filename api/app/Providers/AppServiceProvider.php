<?php

namespace App\Providers;

use App\Services\BlueskyClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BlueskyClient::class, function ($app) {
            $config = $app->make('config')->get('services.bluesky', []);

            return new BlueskyClient(
                baseUrl: rtrim($config['base_url'] ?? 'https://bsky.social', '/'),
                timeoutSeconds: (int) ($config['timeout'] ?? 10),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
