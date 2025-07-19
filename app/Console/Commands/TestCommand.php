<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   // protected $signature = 'app:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $signature = 'test:command'; // <-- This is what you'll run

    public function handle()
    {
        // Simple output
        $this->info('This is an info message');
        $this->line('This is a plain line');
        $this->error('This is an error message');
    }
}
