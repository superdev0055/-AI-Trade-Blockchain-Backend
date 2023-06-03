<?php

namespace App\NewLogics\Pledges;

use App\Enums\CoinSymbolEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Helpers\Web3\HashDataModel;
use App\Models\Users;
use App\Models\Web3Transactions;
use App\NewServices\UsersServices;
use App\NewServices\Web3TransactionsServices;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;

class AutomaticStakingApproveLogics
{
    /**
     * @ok
     * @param Users $user
     * @param string|null $hash
     * @return void
     * @throws Err
     */
    public static function Create(Users $user, ?string $hash): void
    {
        if (!$hash)
            Err::Throw(__("You haven't finish the web3 transaction."));
        Web3TransactionsServices::CreateByAutomaticStaking($user, $hash);
    }

    /**
     * @param Web3Transactions $web3
     * @param HashDataModel $hashData
     * @return void
     * @throws Err
     */
    public static function Web3Callback(Web3Transactions $web3, HashDataModel $hashData): void
    {
        try {
            DB::beginTransaction();

            // from_address
            if (strtolower($web3->from_address) != strtolower($hashData->from_address))
                Err::Throw(__("From address is wrong."));

            // to_address
            if (strtolower($web3->to_address) != strtolower($hashData->to_address))
                Err::Throw(__("To address is wrong."));

            // coin_address
            if ($web3->coin_symbol != strtolower(CoinSymbolEnum::ETH->name) && $web3->coin_symbol != strtolower(CoinSymbolEnum::TRX->name))
                if (strtoupper($web3->coin_address) != strtoupper($hashData->coin_address))
                    Err::Throw(__("Coin address is wrong."));

            // update web3
            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->status = Web3TransactionsStatusEnum::SUCCESS->name;
            $web3->save();

            Log::info("AutomaticStakingApproveLogics::Web3Callback() Success");
            DB::commit();
        } catch (Exception $exception) {
            Log::error("AutomaticStakingApproveLogics::Web3Callback(web3) Error:::", [$web3->toArray()]);
            DB::rollBack();

            $user = UsersServices::GetById($web3->users_id);
            $user->can_automatic_staking = false;
            $user->staking_type = null;
            $user->save();

            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->message = $exception->getMessage();
            $web3->status = Web3TransactionsStatusEnum::ERROR->name;
            $web3->save();
            throw $exception;
        }
    }
}
