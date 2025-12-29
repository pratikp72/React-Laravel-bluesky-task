<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPost extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'bluesky_account_id',
        'content',
        'publish_at',
        'queued_at',
        'status',
        'remote_uri',
        'failure_reason',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'queued_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(BlueskyAccount::class, 'bluesky_account_id');
    }

    public function scopeDue($query)
    {
        return $query
            ->where('publish_at', '<=', now())
            ->where(function ($subQuery) {
                $subQuery->where('status', self::STATUS_SCHEDULED)
                    ->orWhere(function ($retryQuery) {
                        $retryQuery->where('status', self::STATUS_QUEUED)
                            ->where('queued_at', '<=', now()->subMinutes(5));
                    });
            });
    }

    public function markQueued(): void
    {
        $this->forceFill([
            'status' => self::STATUS_QUEUED,
            'queued_at' => now(),
        ])->save();
    }

    public function markSent(?string $remoteUri): void
    {
        $this->forceFill([
            'status' => self::STATUS_SENT,
            'remote_uri' => $remoteUri,
            'failure_reason' => null,
        ])->save();
    }

    public function markFailed(string $reason): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
        ])->save();
    }

    public function cancel(): void
    {
        $this->forceFill([
            'status' => self::STATUS_CANCELLED,
        ])->save();
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_CANCELLED], true);
    }
}
