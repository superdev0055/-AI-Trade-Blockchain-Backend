<?php

namespace App\Console\Commands;

use App\NewServices\VipsServices;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CleanCacheCommand extends Command
{
    protected $signature = 'CleanCacheCommand';

    /**
     * @return int
     */
    public function handle(): int
    {
        VipsServices::CleanVipCache();
        return CommandAlias::SUCCESS;
    }
}
