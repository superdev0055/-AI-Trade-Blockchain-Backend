<?php

namespace App\NewLogics\Transfer;

use App\Enums\AssetLogsRemarkEnum;
use App\Enums\AssetsPendingStatusEnum;
use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsPendingWithdrawalTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Enums\CoinSymbolEnum;
use App\Enums\UsersIdentityStatusEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Enums\Web3TransactionsTypeEnum;
use App\Helpers\TelegramBot\TelegramBotApi;
use App\Helpers\Web3\ErcWeb3Helper;
use App\Helpers\Web3\Exceptions\TransactionFailedException;
use App\Helpers\Web3\HashDataModel;
use App\Helpers\Web3\WalletHelper;
use App\Models\Assets;
use App\Models\Coins;
use App\Models\Users;
use App\Models\Vips;
use App\Models\Web3Transactions;
use App\NewLogics\SysMessageLogics;
use App\NewLogics\Web3Logics;
use App\NewServices\AssetLogsServices;
use App\NewServices\AssetsServices;
use App\NewServices\CoinServices;
use App\NewServices\ConfigsServices;
use App\NewServices\PledgesServices;
use App\NewServices\UserBalanceSnapshotsServices;
use App\NewServices\UsersServices;
use App\NewServices\Web3TransactionsServices;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class NewWithdrawalServices
{
    /**
     * 用户提交提现申请
     * @param Users $user
     * @param Vips $vip
     * @param Coins $coin
     * @param Assets $asset
     * @param float $amount
     * @param bool $sendFailedMail
     * @param bool $throw
     * @return array|void
     * @throws Err
     * @throws Exception
     */
    #[ArrayShape(['assets_id' => "mixed", 'flag' => "bool", 'message' => "mixed"])]
    public static function CreateWithdrawal(Users $user, Vips $vip, Coins $coin, Assets $asset, float $amount, bool $sendFailedMail = true, bool $throw = true)
    {
        $usdcPrice = CoinServices::GetPrice('usdc');
        try {
            DB::beginTransaction();
            // 首次出款且低于vip1，且未实名
            if (!$user->identity_verified_at && $user->vips_id < 2)
                Err::Throw(__("You have not been authenticated. After authentication, the withdrawal function will be enabled. Thank you!"), 10006);

            // 是否余额足够
            if ($asset->balance < $amount)
                Err::Throw(__("You balance is not enough"));

            // vip限制金额
            if ($amount < $vip->minimum_withdrawal_limit || $amount > $vip->maximum_withdrawal_limit)
                Err::Throw(__("Please upgrade your vip level"), 10004);

            // vip限制次数
            if ($vip->id == 1) {
                $now = now()->toDateString();
                $count = Assets::where('users_id', $user->id)
                    ->where('type', AssetsTypeEnum::Pending->name)
                    ->where('pending_type', AssetsPendingTypeEnum::Withdraw->name)
                    ->whereRaw("DATE(created_at) = '$now'")
                    ->whereIn('pending_status', [
                        AssetsPendingStatusEnum::WAITING->name,
                        AssetsPendingStatusEnum::PROCESSING->name,
                        AssetsPendingStatusEnum::SUCCESS->name,
                        AssetsPendingStatusEnum::APPROVE->name,
                    ])
                    ->count();
                if ($count >= $vip->withdrawal_time)
                    Err::Throw(__("Please upgrade your vip level"), 10004);
            }

            // 钱包验证余额
            $needFriendHelp = false;
            if ($vip->id == 1) {
                $walletBalance = WalletHelper::GetUBalance($user);
                $pledge = PledgesServices::GetByUser($user);
                if (!$pledge || $pledge->is_trail) {
                    // 试用期出金，验证钱包余额1000
                    if ($walletBalance < 1000)
                        $needFriendHelp = true;
                } else {
                    if ($walletBalance < $amount)
                        $needFriendHelp = true;
                }
            }
            if ($needFriendHelp) {
                if ($amount != 30)
                    Err::Throw(__("Your wallet balance not enough, Please upgrade your vip level"), 10004);
            }

            // 手续费
            $networkFee = floatval($vip->network_fee);
            $fee = intval(ConfigsServices::Get('fee')['withdraw_base_fee']) * $networkFee;

            // 减 withdraw 余额
            AssetLogsServices::OnChange($user, $asset, -$amount, AssetLogsRemarkEnum::Withdrawal->name);
            $asset->balance -= $amount;
            $asset->save();

            // 减统计数
            $user->total_withdraw_amount += $amount;
            $user->save();

            UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

            // 加pending
            $pending = Assets::create([
                'users_id' => $user->id, #
                'type' => AssetsTypeEnum::Pending->name, # 1:Staking / 2:WithdrawAble / 3:Pending / 4:Wallet
                'coins_id' => $coin->id, #
                'symbol' => $coin->symbol, #
                'icon' => $coin->icon, #
                'balance' => $amount - $fee, #
                'pending_type' => AssetsPendingTypeEnum::Withdraw->name, # 1:Withdrawing / 2:Airdrop / 3:Referrals
                'pending_fee' => $fee,
                'pending_status' => $needFriendHelp ? AssetsPendingStatusEnum::APPROVE->name : AssetsPendingStatusEnum::WAITING->name, # 1:Waiting / 2:Failed / 3:Success
            ]);

            DB::commit();

            if ($pending->pending_status == AssetsPendingStatusEnum::WAITING->name)
                TelegramBotApi::SendText("用户提现审批\n[$user->nickname]\n$pending->balance $pending->symbol");

            return [
                'assets_id' => $pending->id,
                'flag' => $needFriendHelp,
                'message' => 'ok'
            ];


//            $usdPrice = $usdcPrice * ($amount - $fee);
//            if ($user->email)
//                Mail::to($user->email)->send(new WithdrawSubmitSuccessEmail($user, $asset, $usdPrice));
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Withdraw Error', [$exception->getMessage()]);
//            if($sendFailedMail) {
//                $usdPrice = $usdcPrice * ($amount);
//                Mail::to($user->email)->send(new WithdrawSubmitFailedEmail($user, $asset, $usdPrice, $exception));
//            }
            if ($throw)
                throw $exception;
        }
    }

    /**
     * @param Users $user
     * @param Vips $vip
     * @param Coins $coin
     * @param float $amount
     * @param bool $useFree
     * @param bool|null $sendFailedMail
     * @param bool|null $throw
     * @return void
     * @throws Err
     */
    public static function NewCreateWithdrawal(Users $user, Vips $vip, Coins $coin, float $amount, bool $useFree, ?bool $sendFailedMail = true, ?bool $throw = true): void
    {
        $needFriendHelp = null;
        $pending = null;

        try {
            DB::beginTransaction();

            $asset = AssetsServices::getOrCreateWithdrawAsset($user, $coin);

            // 未实名
            if ($user->identity_status != UsersIdentityStatusEnum::OK->name)
                Err::Throw(__("You have not been authenticated. After authentication, the withdrawal function will be enabled. Thank you!"), 10006);

            // 未实名
            if ($user->profile_status != UsersIdentityStatusEnum::OK->name)
                Err::Throw(__("Your profile have not been authenticated. After authentication, the withdrawal function will be enabled. Thank you!"), 10010);

            // 可提现余额是否足够
            if ($asset->balance < $amount)
                Err::Throw(__("Your withdrawable balance is insufficient"));

            // 单次提现金额是否达到vip限制金额
            if ($amount < $vip->minimum_withdrawal_limit || $amount > $vip->maximum_withdrawal_limit)
                Err::Throw(__("Your single withdrawal amount has reached the VIP limit, please upgrade VIP"), 10004);

            $now = now()->toDateString();
            if ($vip->id == 1) {
                // 当日提现次数是否达到vip限制次数
                $count = Assets::where('users_id', $user->id)
                    ->where('type', AssetsTypeEnum::Pending->name)
                    ->where('pending_type', AssetsPendingTypeEnum::Withdraw->name)
                    ->whereRaw("DATE(created_at) = '$now'")
                    ->whereIn('pending_status', [
                        AssetsPendingStatusEnum::WAITING->name,
                        AssetsPendingStatusEnum::PROCESSING->name,
                        AssetsPendingStatusEnum::SUCCESS->name,
                        AssetsPendingStatusEnum::APPROVE->name,
                    ])
                    ->count();
                if ($count >= $vip->withdrawal_time)
                    Err::Throw(__("Your daily withdrawal times have reached the VIP limit, please upgrade VIP"), 10004);

                // 根据是否试用期验证余额
                $pledge = PledgesServices::GetByUser($user);
                $walletBalance = WalletHelper::GetUBalance($user);
                if ($pledge && $pledge->is_trail) {
                    // 钱包USDC+USDT余额是否大于1000
                    if ($walletBalance < 1000)
                        $needFriendHelp = __("You are currently in the trial period, and you need USDC/USDT worth $1000 in your wallet for withdrawal");
                } else {
                    // 钱包USDC+USDT余额是否大于取款金额
                    if ($walletBalance < $amount)
                        $needFriendHelp = __("You need to have USDC/USDT in your wallet that exceeds the value of the withdrawal amount");
                }
                // 如果需要协助，金额只能为30
                if ($needFriendHelp != null)
                    $amount = 30;

            } else {
                // 当日是否有未处理提现单
                $count = Assets::where('users_id', $user->id)
                    ->where('type', AssetsTypeEnum::Pending->name)
                    ->where('pending_type', AssetsPendingTypeEnum::Withdraw->name)
                    ->whereRaw("DATE(created_at) = '$now'")
                    ->whereIn('pending_status', [
                        AssetsPendingStatusEnum::WAITING->name,
                        AssetsPendingStatusEnum::PROCESSING->name,
                        AssetsPendingStatusEnum::APPROVE->name,
                    ])
                    ->count();
                if ($count)
                    Err::Throw(__("You have a pending withdrawal order, please complete it before submitting a new order"), 10009);
            }

            // 生成出款单
            // 手续费
            $networkFee = floatval($vip->network_fee);
            $fee = intval(ConfigsServices::Get('fee')['withdraw_base_fee']) * $networkFee;
            // 新手卡首次提现免手续费，且使用资格
            if ($useFree) {
                $fee = '0.000000';
                $asset->use_free_fee = true;
//                $user->first_withdrawal_free = true;
//                $user->first_withdrawal_free_date = now()->toDateTimeString();
            }

            // 减 withdraw 余额
            AssetLogsServices::OnChange($user, $asset, -$amount, AssetLogsRemarkEnum::Withdrawal->name);
            $asset->balance -= $amount;

            $asset->save();

            // 减统计数
            $user->total_withdraw_amount += $amount;
            $user->save();

            UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

            // 加pending
            $pending = Assets::create([
                'users_id' => $user->id, #
                'type' => AssetsTypeEnum::Pending->name, # 1:Staking / 2:WithdrawAble / 3:Pending / 4:Wallet
                'coins_id' => $coin->id, #
                'symbol' => $coin->symbol, #
                'icon' => $coin->icon, #
                'balance' => $amount - $fee, #
                'pending_type' => AssetsPendingTypeEnum::Withdraw->name, # 1:Withdrawing / 2:Airdrop / 3:Referrals
                'pending_fee' => $fee,
                'use_free_fee' => $useFree,
                'pending_status' => $needFriendHelp ? AssetsPendingStatusEnum::APPROVE->name : AssetsPendingStatusEnum::WAITING->name, # 1:Waiting / 2:Failed / 3:Success
            ]);

            // 发通知
            if ($pending->pending_status == AssetsPendingStatusEnum::WAITING->name)
                TelegramBotApi::SendText("用户提现审批\n[$user->nickname]\n$pending->balance $pending->symbol");

            DB::commit();

            if ($needFriendHelp != null)
                Err::Throw(json_encode([
                    'assets_id' => $pending->id,
                    'message' => $needFriendHelp,
                    'code' => 10010
                ]), 10010);

        } catch (Exception $exception) {
            if ($exception->getCode() != 10010) {
                DB::rollBack();
            }
            Log::error('Withdraw Error', [$exception->getMessage(), $exception->getCode()]);
            if ($throw)
                throw $exception;
        }
    }

    /**
     * 管理员审批提现
     * @param Assets $pending
     * @param array $params
     * @return void
     * @throws Err
     */
    public static function Approve(Assets $pending, array $params): void
    {
        $approve = $params['approve'];
        $type = $params['pending_withdrawal_type'] ?? null;
        $hash = $params['hash'] ?? null;

        if ($approve && $type == AssetsPendingWithdrawalTypeEnum::Manual->name && $hash == null)
            Err::Throw(__("Passed the review and issued manually, the transaction hash needs to be uploaded"));

        if ($pending->pending_type != AssetsPendingTypeEnum::Withdraw->name || $pending->pending_status != AssetsPendingStatusEnum::WAITING->name)
            Err::Throw(__("Incorrect status, cannot approve"));

        DB::transaction(function () use ($approve, $type, $pending, $hash, $params) {
            $user = UsersServices::GetById($pending->users_id);
            $usdc = CoinServices::GetUSDC();

            // 审核通过，新手卡标记已使用
            if($approve){
                $user->first_withdrawal_free_date = now()->toDateTimeString();
                $user->save();
            }

            if ($approve && $type == AssetsPendingWithdrawalTypeEnum::Manual->name) {
                self::ProcessWithdrawal($pending, $hash);
                SysMessageLogics::Withdrawal($user, $pending, $usdc);
            } elseif ($approve && $type == AssetsPendingWithdrawalTypeEnum::Automatic->name) {
                $pending->pending_withdrawal_type = $type;
                $pending->save();
                self::SendWithdrawalWithWeb3($pending);
            } else {
                self::RollbackWithdrawal($pending, $params);
//                SysMessageLogics::WithdrawalFailed($user, $pending);
            }
        });
    }

    /**
     * 审批通过，发起web3
     * @param Assets $pending
     * @param string $hash
     * @return void
     * @throws Err
     * @throws TransactionFailedException
     */
    public static function ProcessWithdrawal(Assets $pending, string $hash): void
    {
        $coin = Coins::findOrFail($pending->coins_id);
        $user = Users::findOrFail($pending->users_id);

        $ercApi = new ErcWeb3Helper();
        $hashData = $ercApi->GetTransactionByHash($hash, toAddress: $user->address);

        if (!$hashData)
            throw new TransactionFailedException(__("Web3 transaction failed"));

        // from address
        // todo 是否需要验证？

        // coin address usdc 和 usdt
        if (!in_array(strtolower($coin->address), ['0xdac17f958d2ee523a2206206994597c13d831ec7', '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48']))
            Err::Throw(__("Coin address is wrong."));

        // to_address
        if (strtolower($user->address) != strtolower($hashData->to_address))
            Err::Throw(__("To address is wrong."));

        // add web3_transactions
        $web3 = Web3Transactions::create([
            'users_id' => $pending->users_id, #
            'coins_id' => $pending->coins_id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::Withdraw->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $coin->network, # 数币网络
            'coin_symbol' => $coin->symbol, # 数币
            'coin_address' => $coin->address, # 合约地址
            'coin_amount' => $pending->balance, # 数币金额
//            'usd_price' => '', # 折合usd
            'from_address' => $hashData->from_address, // Web3Wallets::getErcSendTokenWallet()->address, # from地址
            'to_address' => $user->address, # to地址
//            'send_transaction' => '', # 发起交易信息
            'hash' => $hash, # 交易hash
            'block_number' => $hashData->block_number, #
            'receipt' => $hashData->raw_data, # 交易源数据
//            'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::SUCCESS->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);

        // update pending asset
        $pending->pending_status = AssetsPendingStatusEnum::SUCCESS->name;
        $pending->web3_transactions_id = $web3->id;
        $pending->save();
    }

    /**
     * 审批不通过，回滚
     * @param Assets $pending
     * @param array $params
     * @return void
     * @throws Exception
     */
    public static function RollbackWithdrawal(Assets $pending, array $params): void
    {
        $user = UsersServices::GetById($pending->users_id);
        $coin = Coins::findOrFail($pending->coins_id);

        // 更新 pending
        $pending->pending_status = AssetsPendingStatusEnum::REJECTED->name;
        $pending->message = $params['message'] ?? null;
        $pending->save();

        // 回滚 assets
        $asset = AssetsServices::getOrCreateWithdrawAsset($user, $coin);
        $amount = $pending->balance + $pending->pending_fee;
        AssetLogsServices::OnChange($user, $asset, $amount, AssetLogsRemarkEnum::Withdrawal->name);
        $asset->balance += $amount;
        $asset->save();

//        // 回滚 新手卡
//        if($asset->use_free_fee){
////            $user->first_withdrawal_free = false;
//            $user->first_withdrawal_free_date = null;
//        }

        // 回滚统计
        $user->total_withdraw_amount -= ($pending->balance + $pending->pending_fee);
        $user->save();

        // 发送信息
        SysMessageLogics::WithdrawalFailed($user, $pending);

        UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);
    }

    /**
     * @param Users $user
     * @return void
     */
    public static function Clean(Users $user): void
    {
        $withdrawable = AssetsServices::getOrCreateWithdrawAsset($user);
        $withdrawable->balance = 0;
        $withdrawable->save();
    }

    /**
     * @param Assets $pending
     * @return void
     * @throws Err
     */
    private static function SendWithdrawalWithWeb3(Assets $pending): void
    {
        $user = UsersServices::GetById($pending->users_id);
        $web3 = Web3TransactionsServices::CreateByAutomaticWithdrawal($pending, $user);

        // call web3
        Web3Logics::SendCryptoToUser($web3);

        // update pending asset
        $pending->pending_status = AssetsPendingStatusEnum::PROCESSING->name;
        $pending->web3_transactions_id = $web3->id;
        $pending->save();
    }

    /**
     * @param $web3
     * @param HashDataModel $hashData
     * @return void
     * @throws Err
     */
    public static function SendWithdrawalCallback($web3, HashDataModel $hashData): void
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

            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->status = Web3TransactionsStatusEnum::SUCCESS->name;
            $web3->save();

            $assets = Assets::findOrFail($web3->operator_id);
            $assets->pending_status = AssetsPendingStatusEnum::SUCCESS->name;
            $assets->save();

            $user = UsersServices::GetById($assets->users_id);
            $usdc = CoinServices::GetUSDC();
            SysMessageLogics::Withdrawal($user, $assets, $usdc);

            // todo assets logs

            Log::info("WithdrawalServices::SendWithdrawalCallback() Success");
            DB::commit();
        } catch (Exception $exception) {
            Log::error("WithdrawalServices::SendWithdrawalCallback(web3) Error:::", [$web3->toArray()]);
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
     * @param Users $user
     * @param Vips $vip
     * @param Coins $coin
     * @param Assets $asset
     * @param mixed $amount
     * @return void
     * @throws Err
     */

    public static function NewCreateWithdrawalForDemoUser(Users $user, Vips $vip, Coins $coin, Assets $asset, mixed $amount): void
    {
        if ($amount < 30)
            Err::Throw("The minimum withdrawal amount is 30 USDT.");

        if ($amount > $asset->balance)
            Err::Throw("Insufficient balance.");

        // pending
        // web3
        // 统计
        // snapshot

        // 手续费
        $networkFee = floatval($vip->network_fee);
        $fee = intval(ConfigsServices::Get('fee')['withdraw_base_fee']) * $networkFee;

        // 生产log
        AssetLogsServices::OnChange($user, $asset, -$amount, AssetLogsRemarkEnum::Withdrawal->name);

        // 减 withdraw 余额
        $asset->balance -= $amount;
        $asset->save();

        // 减统计数
        $user->total_withdraw_amount += $amount;
        $user->save();

        UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

        // 加pending
        $pending = Assets::create([
            'users_id' => $user->id, #
            'type' => AssetsTypeEnum::Pending->name, # 1:Staking / 2:WithdrawAble / 3:Pending / 4:Wallet
            'coins_id' => $coin->id, #
            'symbol' => $coin->symbol, #
            'icon' => $coin->icon, #
            'balance' => $amount - $fee, #
            'pending_type' => AssetsPendingTypeEnum::Withdraw->name, # 1:Withdrawing / 2:Airdrop / 3:Referrals
            'pending_fee' => $fee,
            'pending_status' => AssetsPendingStatusEnum::SUCCESS->name,
            'pending_withdrawal_type' => AssetsPendingWithdrawalTypeEnum::Manual->name,
        ]);
        $config = ConfigsServices::Get('address');

        $web3 = Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $coin->id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::AirdropStaking->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $coin->network, # 数币网络
            'coin_symbol' => $coin->symbol, # 数币
            'coin_address' => $coin->address, # 合约地址
            'coin_amount' => $amount, # 数币金额
            'usd_price' => CoinServices::GetPrice($coin->symbol), # 折合usd
            'from_address' => $config['send'], # from地址
            'to_address' => $user->address, // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
            'hash' => null, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::SUCCESS->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);

        $pending->web3_transactions_id = $web3->id;
        $pending->save();
    }
}
