<?php

namespace App\Console\Commands;

use App\Enums\CoinNetworkEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Enums\Web3TransactionsTypeEnum;
use App\Helpers\Web3\ErcWeb3Helper;
use App\Helpers\Web3\Exceptions\TransactionFailedException;
use App\Models\Users;
use App\Models\Web3Transactions;
use App\NewLogics\StakingRewardLoyaltyLogics;
use App\NewLogics\Pledges\AutomaticStakingApproveLogics;
use App\NewLogics\Pledges\DepositPledgeProfitLogics;
use App\NewLogics\Transfer\ExchangeAirdropLogics;
use App\NewLogics\Transfer\NewWithdrawalServices;
use App\NewLogics\Transfer\StakingLogics;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RefreshWeb3TransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RefreshWeb3TransactionCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {

        Log::debug("Start RefreshWeb3TransactionJob");

        $ercApi = new ErcWeb3Helper();

        Web3Transactions::where('status', Web3TransactionsStatusEnum::PROCESSING->name)
            ->whereNotNull('hash')
            ->where('type', '!=', Web3TransactionsTypeEnum::Withdraw->name) // 提现不用web3
            ->each(function (Web3Transactions $item) use ($ercApi) {
                if (!$item->hash)
                    return;
                try {
                    // get HashData
                    if ($item->coin_network == CoinNetworkEnum::ERC20->name) {
                        $hashData = $ercApi->GetTransactionByHash($item->hash, toAddress: $item->to_address);
                    }
                    if (!$hashData)
                        throw new TransactionFailedException(__("Web3 transaction failed"));

                    // get User
                    $user = Users::find($item->users_id);
                    if (!$user)
                        Err::Throw(__("User is not exists"));

                    // dispatch callback
                    switch ($item->type) {
                        case Web3TransactionsTypeEnum::DepositStaking->name:
                            DepositPledgeProfitLogics::DepositWeb3Callback($item, $hashData);
                            break;
                        case Web3TransactionsTypeEnum::Staking->name:
                            StakingLogics::StakingCallback($item, $hashData);
                            break;
                        case  Web3TransactionsTypeEnum::AirdropStaking->name:
                            ExchangeAirdropLogics::Web3Callback($item, $hashData);
                            break;
                        case  Web3TransactionsTypeEnum::Approve->name:
                            AutomaticStakingApproveLogics::Web3Callback($item, $hashData);
                            break;
                        case Web3TransactionsTypeEnum::AutomaticWithdraw->name:
                            NewWithdrawalServices::SendWithdrawalCallback($item, $hashData);
                            break;
                        case Web3TransactionsTypeEnum::StakingRewardLoyalty->name:
                            StakingRewardLoyaltyLogics::Web3Callback($item, $hashData);
                            break;
                    }
                    Log::debug("$item->id...$item->hash...Success");
                } catch (TransactionFailedException $exception) {
                    Log::debug("$item->id...$item->hash...Error 1 :::{$exception->getMessage()}");
                    $item->message = $exception->getMessage();
                    $item->status = Web3TransactionsStatusEnum::ERROR->name;
                    $item->save();
//                    throw $exception;
                } catch (Exception $exception) {
                    Log::debug("$item->id...$item->hash...Error 2 :::{$exception->getMessage()}");
                }
            });
        return CommandAlias::SUCCESS;
    }
}
