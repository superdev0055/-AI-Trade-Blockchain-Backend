<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ResetTodayHadHelpCountCommand extends Command
{
    protected $signature = 'ResetTodayHadHelpCountCommand';
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {
        DB::table('users')->update([
            'today_had_help_count' => 0
        ]);
        return CommandAlias::SUCCESS;
    }
}
