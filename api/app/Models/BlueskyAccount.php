<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlueskyAccount extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONNECTED = 'connected';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'label',
        'handle',
        'service',
        'status',
        'app_password',
        'did',
        'access_jwt',
        'refresh_jwt',
        'last_authenticated_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_authenticated_at' => 'datetime',
        'app_password' => 'encrypted',
        'access_jwt' => 'encrypted',
        'refresh_jwt' => 'encrypted',
    ];

    protected $hidden = [
        'app_password',
        'access_jwt',
        'refresh_jwt',
    ];

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public function markConnected(array $session): void
    {
        $this->forceFill([
            'status' => self::STATUS_CONNECTED,
            'did' => $session['did'] ?? $this->did,
            'access_jwt' => $session['accessJwt'] ?? $this->access_jwt,
            'refresh_jwt' => $session['refreshJwt'] ?? $this->refresh_jwt,
            'last_authenticated_at' => now(),
            'meta' => [
                'handle' => $session['handle'] ?? $this->handle,
            ],
        ])->save();
    }
}
