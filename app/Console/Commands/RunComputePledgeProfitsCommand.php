<?php

namespace App\Console\Commands;

use App\Helpers\CoinGecko\CGProHelper;
use App\NewLogics\Pledges\ComputePledgesProfitsLogics;
use App\NewServices\JackpotsHasUsersServices;
use App\NewServices\JackpotsServices;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RunComputePledgeProfitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RunComputePledgeProfitsCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @return int
     * @throws BindingResolutionException
     * @throws Err
     * @throws Exception
     */
    public function handle(): int
    {
        Log::debug(str_repeat('=', 50));

        $jackpot = JackpotsServices::Get();

        // 缓存所有币价
        CGProHelper::SetAllCoinsPriceToCache();
        Log::debug("CoinServices::CacheAllCoinsPrice()...DONE");

        // 计算所有的pledge profits
        ComputePledgesProfitsLogics::ComputeAll($jackpot);
        Log::debug("PledgesProfit::Compute()...DONE");

        // 计算jackpots的airdrop、rank
        JackpotsHasUsersServices::RefreshWhenRoundFinished($jackpot);
        Log::debug("JackpotsHasUsersServices::RefreshWhenRoundFinished()...DONE");

        // 计算jackpots的结束，发放空投
        JackpotsServices::WhenJackpotFinished($jackpot);

        Log::debug(str_repeat('=', 50));

        return CommandAlias::SUCCESS;
    }
}
