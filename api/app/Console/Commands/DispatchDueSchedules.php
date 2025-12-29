<?php

namespace App\Console\Commands;

use App\Jobs\PublishScheduledPost;
use App\Models\ScheduledPost;
use Illuminate\Console\Command;

class DispatchDueSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bluesky:dispatch-due {--chunk=50 : Maximum number of due posts to dispatch per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch queued Bluesky posts whose publish time has arrived';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('chunk');

        $duePosts = ScheduledPost::query()
            ->due()
            ->with('account')
            ->limit($limit)
            ->get();

        foreach ($duePosts as $post) {
            $post->markQueued();
            PublishScheduledPost::dispatch($post->id);
        }

        $this->info("Dispatched {$duePosts->count()} scheduled posts.");

        return Command::SUCCESS;
    }
}
