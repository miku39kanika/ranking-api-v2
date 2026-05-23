<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ExpirePlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::where('plan_type', 1)
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', now())
            ->update([
                'plan_type' => 0,
                'plan_expires_at' => null,
            ]);

        return Command::SUCCESS;
    }
}
