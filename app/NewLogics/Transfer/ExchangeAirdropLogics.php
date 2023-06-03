<?php

namespace App\NewLogics\Transfer;

use App\Enums\AssetsPendingStatusEnum;
use App\Enums\CoinSymbolEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Helpers\Web3\HashDataModel;
use App\Models\Assets;
use App\Models\Users;
use App\Models\Web3Transactions;
use App\NewServices\AssetsServices;
use App\NewServices\BonusesServices;
use App\NewServices\CoinServices;
use App\NewServices\ConfigsServices;
use App\NewServices\PledgesServices;
use App\NewServices\UserBalanceSnapshotsServices;
use App\NewServices\UsersServices;
use App\NewServices\Web3TransactionsServices;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class ExchangeAirdropLogics
{
    /**
     * @param Users $user
     * @return void
     */
    public static function Clean(Users $user): void
    {
        $airdrop = AssetsServices::getOrCreateAirdropAsset($user);
        $airdrop->balance = 0;
        $airdrop->save();
    }

    /**
     * @param Users $user
     * @param Assets $asset
     * @param float $amount
     * @param array $config
     * @return void
     * @throws Err
     */
    public static function Can(Users $user, Assets $asset, float $amount, array $config): void
    {
        if ($asset->balance < $amount)
            Err::Throw(__("Your airdrop balance is not enough"));

        $min = $config['min'];
        if ($amount < $min)
            Err::Throw(__("The exchange amount is less then min"));

        $pledge = PledgesServices::GetByUser($user);
        if ($pledge && $pledge->is_trail)
            Err::Throw(__("Your can not exchange airdrop when you are in trailing"));

    }

    /**
     * @param Users $user
     * @param float $amount
     * @param string $hash
     * @return void
     * @throws Exception
     */
    public static function CreateOrder(Users $user, float $amount, string $hash): void
    {
        CommonHelper::Trans(function () use ($user, $amount, $hash) {
            Web3TransactionsServices::HashIsExists($hash);

            $config = ConfigsServices::Get('gift');
            $usdc = CoinServices::GetUSDC();

            // get airdrop asset
            $airdrop = AssetsServices::getOrCreateAirdropAsset($user);

            // can exchange?
            self::Can($user, $airdrop, $amount, $config);

            // sub airdrop asset
            $airdrop->balance -= $amount;
            $airdrop->save();

            // create pending assets
            $pending = AssetsServices::CreateAirdropPendingAsset($user, $usdc, $amount);

            // create web3 transactions
            $web3 = Web3TransactionsServices::CreateByExchangeAirdrop($pending, $user, $usdc, $amount, $hash);

            $pending->web3_transactions_id = $web3->id;
            $pending->save();
        });
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

            // coin_amount
            if ($web3->coin_amount != $hashData->coin_amount)
                Err::Throw(__("Wrong amount value"));

            // update web3
            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->status = Web3TransactionsStatusEnum::SUCCESS->name;
            $web3->save();

            // update pending
            $pending = Assets::findOrFail($web3->operator_id);
            $pending->pending_status = AssetsPendingStatusEnum::SUCCESS->name;
            $pending->save();

            $user = UsersServices::GetUserById($pending->users_id);
            self::ProcessExchange($pending, $user);

            // refresh balance
            UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

            // bonus
            BonusesServices::CreateByReferrals($user);

            Log::info("ExchangeAirdropLogics::Web3Callback() Success");
            DB::commit();
        } catch (Exception $exception) {
            Log::error("ExchangeAirdropLogics::Web3Callback(web3) Error:::", [$web3->toArray()]);
            DB::rollBack();
            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->message = $exception->getMessage();
            $web3->status = Web3TransactionsStatusEnum::ERROR->name;
            $web3->save();
            throw $exception;
        }
    }

    /**
     * @param Assets $pending
     * @param Users $user
     * @return void
     * @throws Err
     */
    public static function ProcessExchange(Assets $pending, Users $user): void
    {
        // 1个进Staking
        StakingLogics::ProcessStaking($pending, $user);

        // 另1个也进Staking
        StakingLogics::ProcessStaking($pending, $user);
    }
}
