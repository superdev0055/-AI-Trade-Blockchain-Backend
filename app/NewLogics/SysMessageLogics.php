<?php

namespace App\NewLogics;

use App\Enums\SysMessageTypeEnum;
use App\Models\Assets;
use App\Models\CaseDetails;
use App\Models\Coins;
use App\Models\GiftDetails;
use App\Models\JackpotsHasUsers;
use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\SysMessages;
use App\Models\Users;
use App\Models\Vips;
use App\NewServices\CoinServices;
use App\NewServices\UsersServices;
use Exception;

class SysMessageLogics
{
    /**
     * @param Users $user
     * @param Vips $vip
     * @return void
     */
    public static function VipUpgrade(Users $user, Vips $vip): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::VipUpgrade->name, # 类型
            'content' => json_encode([
                'vip' => $vip->only('id', 'name')
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @param PledgeProfits $profit
     * @param Coins $usdc
     * @return void
     * @throws Exception
     */
    public static function PledgeMessage(Users $user, PledgeProfits $profit, Coins $usdc): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::PledgeMessage->name, # 类型
            'content' => json_encode([
                'profit' => $profit->only('id', 'round')
            ]), #
            'usdc' => $profit->actual_income, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $profit->actual_income), #
        ]);
    }

    /**
     * @param Users $user
     * @return void
     * @throws Exception
     */
    public static function ProfileVerify(Users $user): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::ProfileVerify->name, # 类型
            'content' => json_encode([
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @return void
     * @throws Exception
     */
    public static function ProfileVerifyFailed(Users $user): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::ProfileVerifyFailed->name, # 类型
            'content' => json_encode([
                'reason' => $user->profile_error_message
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @return void
     */
    public static function IdentityVerify(Users $user): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::IdentityVerify->name, # 类型
            'content' => json_encode([
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @return void
     */
    public static function IdentityVerifyFailed(Users $user): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::IdentityVerifyFailed->name, # 类型
            'content' => json_encode([
                'reason' => $user->identity_error_message
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @param Assets $pending
     * @param Coins $usdc
     * @return void
     * @throws Exception
     */
    public static function Withdrawal(Users $user, Assets $pending, Coins $usdc): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::Withdrawal->name, # 类型
            'content' => json_encode([
                'pending' => $pending->only('id', 'balance', 'pending_fee')
            ]), #
            'usdc' => $pending->balance, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $pending->balance), #
        ]);
    }

    /**
     * @param Users $user
     * @param Assets $pending
     * @return void
     * @throws Exception
     */
    public static function WithdrawalFailed(Users $user, Assets $pending): void
    {
        $usdc = CoinServices::GetUSDC();
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::WithdrawalFailed->name, # 类型
            'content' => json_encode([
                'reason' => $pending->only('id', 'balance', 'pending_fee', 'pending_status', 'message')
            ]), #
            'usdc' => $pending->balance + $pending->pending_fee, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $pending->balance + $pending->pending_fee), #
        ]);
    }

    /**
     * @param Users $fromUser
     * @param Users $toUser
     * @param Assets $pending
     * @return void
     * @throws Exception
     */
    public static function WithdrawalInvite(Users $fromUser, Users $toUser, Assets $pending): void
    {
        $usd = CoinServices::GetTokenUsdPrice(CoinServices::GetUSDC(), $pending->balance);
        SysMessages::create([
            'users_id' => $toUser->id, #
            'type' => SysMessageTypeEnum::WithdrawalInvite->name, # 类型
            'content' => json_encode([
                'pending' => [...$pending->only('id', 'balance'), 'usd' => $usd],
                'fromUser' => $fromUser->only('id', 'address', 'nickname', 'avatar')
            ]), #
        ]);
    }

    /**
     * @ok
     * @param Users $user
     * @param Users $friend
     * @return void
     * @throws Exception
     */
    public static function AddFriend(Users $user, Users $friend): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::AddFriend->name, # 类型
            'content' => json_encode([
                'friend' => $friend->only('id', 'address', 'nickname', 'avatar')
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @param Assets $pending
     * @param Users $friend
     * @return void
     */
    public static function FriendHelpWithdrawal(Users $user, Assets $pending, Users $friend): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::FriendHelpWithdrawal->name, # 类型
            'content' => json_encode([
                'pending' => $pending->only('id', 'balance', 'pending_fee'),
                'friend' => $friend->only('id', 'address', 'nickname', 'avatar')
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @param PledgeProfits $profit
     * @param Coins $usdc
     * @return void
     * @throws Exception
     */
    public static function ResumeOrder(Users $user, PledgeProfits $profit, Coins $usdc): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::ResumeOrder->name, # 类型
            'content' => json_encode([
                'profit' => $profit->only('id', 'round', 'deposit_total_amount', 'deposit_loyalty_amount', 'deposit_staking_amount')
            ]), #
            'usdc' => $profit->deposit_total_amount, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $profit->deposit_total_amount), #
        ]);
    }

    /**
     * @param Users $user
     * @param JackpotsHasUsers $jackpotsHasUser
     * @param Coins $usdc
     * @return void
     * @throws Exception
     */
    public static function Airdrop(Users $user, JackpotsHasUsers $jackpotsHasUser, Coins $usdc): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::Airdrop->name, # 类型
            'content' => json_encode([
                'airdrop' => $jackpotsHasUser->only('id', 'airdrop', 'rank')
            ]), #
            'usdc' => $jackpotsHasUser->airdrop, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $jackpotsHasUser->airdrop), #
        ]);
    }

    /**
     * @param Users $user
     * @param GiftDetails $giftDetail
     * @param Coins $usdc
     * @return void
     * @throws Exception
     */
    public static function GiftReceived(Users $user, GiftDetails $giftDetail, Coins $usdc): void
    {
        $fromUser = UsersServices::GetById($giftDetail->from_users_id);

        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::GiftReceived->name, # 类型
            'content' => json_encode([
                'gift' => $giftDetail->only('id', 'amount'),
                'fromUser' => $fromUser->only('id', 'address', 'nickname', 'avatar')
            ]), #
            'usdc' => $giftDetail->amount, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $giftDetail->amount), #
        ]);
    }

    /**
     * @param Users $user
     * @param CaseDetails $caseDetail
     * @return void
     * @throws Exception
     */
    public static function SupportAnswered(Users $user, CaseDetails $caseDetail): void
    {
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::SupportAnswered->name, # 类型
            'content' => json_encode([
                'case' => $caseDetail->only('id', 'answer')
            ]), #
        ]);
    }

    /**
     * @param Users $user
     * @param float $amount
     * @return void
     * @throws Exception
     */
    public static function Staking(Users $user, float $amount): void
    {
        $usdc = CoinServices::GetUSDC();
        SysMessages::create([
            'users_id' => $user->id, #
            'type' => SysMessageTypeEnum::Staking->name, # 类型
            'content' => json_encode([
//                'asset' => $asset->only('id', 'balance')
            ]),
            'usdc' => $amount, #
            'usd' => CoinServices::GetTokenUsdPrice($usdc, $amount)
        ]);
    }

    /**
     * @param Users $fromUser
     * @param Users $toUser
     * @param mixed $content
     * @return void
     */
    public static function SendFriendMessage(Users $fromUser, Users $toUser, mixed $content): void
    {
        SysMessages::create([
            'users_id' => $toUser->id, #
            'type' => SysMessageTypeEnum::FriendMessage->name, # 类型
            'content' => json_encode([
                'fromUser' => $fromUser->only('id', 'address', 'nickname', 'avatar'),
                'toUser' => $toUser->only('id', 'address', 'nickname', 'avatar'),
                'content' => $content
            ]), #
        ]);
    }

    /**
     * @param Users $toUser
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param float $diff
     * @return void
     */
    public static function LoyaltyNotEnough(Users $toUser, Pledges $pledge, PledgeProfits $profit, float $diff): void
    {
        SysMessages::create([
            'users_id' => $toUser->id, #
            'type' => SysMessageTypeEnum::LoyaltyNotEnough->name, # 类型
            'content' => json_encode([
                'toUser' => $toUser->only('id', 'address', 'nickname', 'avatar'),
                'pledge' => $pledge->only('id'),
                'profit' => $profit->only('id'),
                'amount' => $diff,
            ]), #
        ]);
    }
}
