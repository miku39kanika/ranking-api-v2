<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessVote implements ShouldQueue
{
    use Queueable;
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $itemId
    ) {}

    public function handle()
    {
        DB::table('ranking_items')
            ->where('id', $this->itemId)
            ->increment('votes');
    }
}
