<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDOException;

class DbMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the database connection is available';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::connection()->getPdo();
            return 0; // Success
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1; // Error
        }
    }
}
