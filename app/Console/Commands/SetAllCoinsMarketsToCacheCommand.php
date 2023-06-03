<?php

namespace App\Console\Commands;

use App\Helpers\CoinGecko\CGProHelper;
use Exception;
use Illuminate\Console\Command;

class SetAllCoinsMarketsToCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SetAllCoinsMarketsToCacheCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        CGProHelper::SetAllCoinsMarketsToCache();
        return Command::SUCCESS;
    }
}
