<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property string $name
 * @property numeric $need_stake
 * @property boolean $can_automatic_trade
 * @property boolean $can_trail_bonus
 * @property boolean $can_automatic_exchange
 * @property boolean $can_email_notification
 * @property boolean $can_leveraged_investment
 * @property boolean $can_automatic_loan_repayment
 * @property boolean $can_prevent_liquidation
 * @property boolean $can_profit_guarantee
 * @property boolean $can_automatic_airdrop_bonus
 * @property boolean $can_automatic_staking
 * @property boolean $can_automatic_withdrawal
 * @property integer $daily_referral_rewards
 * @property numeric $level_1_refer
 * @property numeric $level_2_refer
 * @property numeric $level_3_refer
 * @property boolean $can_pm_friends
 * @property boolean $can_customize_online_status
 * @property boolean $can_view_contact_details
 * @property boolean $can_send_gift
 * @property integer $leveraged_investment
 * @property numeric $loan_charges
 * @property numeric $minimum_apy_guarantee
 * @property boolean $can_promotion_first_notice
 * @property boolean $can_exclusive_customer_service
 * @property integer $max_staking_term
 * @property numeric $minimum_withdrawal_limit
 * @property numeric $maximum_withdrawal_limit
 * @property integer $number_of_withdrawals
 * @property integer $withdrawal_time
 * @property numeric $network_fee
 * @property boolean $need_withdrawal_verification
 * @property numeric $max_help_withdraw_amount
 * @property integer $max_help_withdraw_count
 * @property date $created_at
 * @property date $updated_at

 * @method static ifWhere(array $params, string $string)
 * @method static ifWhereLike(array $params, string $string)
 * @method static ifWhereIn(array $params, string $string)
 * @method static ifRange(array $params, string $string)
 * @method static create(array $array)
 * @method static unique(array $params, array $array, string $string)
 * @method static idp(array $params)
 * @method static findOrFail(int $id)
 * @method static selectRaw(string $string)
 * @method static withTrashed()
 
 */
class BaseVips extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'vips';
    protected string $comment = '';
    protected $fillable = ['name', 'need_stake', 'can_automatic_trade', 'can_trail_bonus', 'can_automatic_exchange', 'can_email_notification', 'can_leveraged_investment', 'can_automatic_loan_repayment', 'can_prevent_liquidation', 'can_profit_guarantee', 'can_automatic_airdrop_bonus', 'can_automatic_staking', 'can_automatic_withdrawal', 'daily_referral_rewards', 'level_1_refer', 'level_2_refer', 'level_3_refer', 'can_pm_friends', 'can_customize_online_status', 'can_view_contact_details', 'can_send_gift', 'leveraged_investment', 'loan_charges', 'minimum_apy_guarantee', 'can_promotion_first_notice', 'can_exclusive_customer_service', 'max_staking_term', 'minimum_withdrawal_limit', 'maximum_withdrawal_limit', 'number_of_withdrawals', 'withdrawal_time', 'network_fee', 'need_withdrawal_verification', 'max_help_withdraw_amount', 'max_help_withdraw_count'];


    # region relations
    public function pledge_profits(): Relations\HasMany
    {
        return $this->hasMany(Models\PledgeProfits::class, 'vips_id', 'id');
    }
    public function users(): Relations\HasMany
    {
        return $this->hasMany(Models\Users::class, 'vips_id', 'id');
    }

    # endregion
}
