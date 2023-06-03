<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum SysMessageTypeEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case VipUpgrade = 'VipUpgrade';
    /**
     * @color blue
     */
    case PledgeMessage = 'PledgeMessage';
    /**
     * @color yellow
     */
    case ProfileVerify = 'ProfileVerify';
    /**
     * @color red
     */
    case ProfileVerifyFailed = 'ProfileVerifyFailed';
    /**
     * @color gray
     */
    case IdentityVerify = 'IdentityVerify';
    /**
     * @color orange
     */
    case IdentityVerifyFailed = 'IdentityVerifyFailed';
    /**
     * @color purple
     */
    case WithdrawalInvite = 'WithdrawalInvite';
    /**
     * @color pink
     */
    case AddFriend = 'AddFriend';
    /**
     * @color brown
     */
    case FriendHelpWithdrawal = 'FriendHelpWithdrawal';
    /**
     * @color black
     */
    case ResumeOrder = 'ResumeOrder';
    /**
     * @color white
     */
    case Airdrop = 'Airdrop';
    /**
     * @color cyan
     */
    case GiftReceived = 'GiftReceived';
    /**
     * @color lime
     */
    case GiftHasReceived = 'GiftHasReceived';
    /**
     * @color teal
     */
    case SupportAnswered = 'SupportAnswered';
    /**
     * @color indigo
     */
    case Withdrawal = 'Withdrawal';
    /**
     * @color violet
     */
    case WithdrawalFailed = 'WithdrawalFailed';
    /**
     * @color maroon
     */
    case Staking = 'Staking';
    /**
     * @color olive
     */
    case FriendMessage = 'FriendMessage';
    /**
     * @color navy
     */
    case LoyaltyNotEnough = 'LoyaltyNotEnough';
}
