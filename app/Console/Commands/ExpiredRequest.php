<?php

namespace App\Console\Commands;

use App\Jobs\ExpiredRequestEmail;
use Illuminate\Console\Command;

class ExpiredRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:expired-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To check expired request';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new ExpiredRequestEmail(true));
        return Command::SUCCESS;
    }
}
