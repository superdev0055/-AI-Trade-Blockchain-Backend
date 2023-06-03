<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property string $platform
 * @property string $address
 * @property string $invite_code
 * @property integer $vips_id
 * @property string $referral_url
 * @property integer $parent_1_id
 * @property integer $parent_2_id
 * @property integer $parent_3_id
 * @property string $email
 * @property date $email_verified_at
 * @property string $avatar
 * @property string $nickname
 * @property string $bio
 * @property string $phone_number
 * @property string $facebook
 * @property string $telegram
 * @property string $wechat
 * @property string $skype
 * @property string $whatsapp
 * @property string $line
 * @property string $zalo
 * @property date $profile_verified_at
 * @property string $profile_status
 * @property string $profile_error_message
 * @property date $profile_error_last_at
 * @property integer $profile_error_count_today
 * @property string $full_name
 * @property string $id_no
 * @property string $country
 * @property string $city
 * @property string $id_front_img
 * @property string $id_reverse_img
 * @property string $self_photo_img
 * @property date $identity_verified_at
 * @property string $identity_status
 * @property string $identity_error_message
 * @property date $identity_error_last_at
 * @property integer $identity_error_count_today
 * @property boolean $can_automatic_trade
 * @property boolean $can_trail_bonus
 * @property boolean $can_automatic_exchange
 * @property boolean $can_email_notification
 * @property boolean $can_leveraged_investment
 * @property boolean $can_automatic_loan_repayment
 * @property boolean $can_prevent_liquidation
 * @property numeric $prevent_liquidation_amount
 * @property boolean $can_profit_guarantee
 * @property boolean $can_automatic_airdrop_bonus
 * @property boolean $can_automatic_staking
 * @property string $staking_type
 * @property boolean $can_automatic_withdrawal
 * @property numeric $automatic_withdrawal_amount
 * @property boolean $can_say
 * @property numeric $staking
 * @property numeric $withdrawable
 * @property numeric $total_balance
 * @property numeric $total_rate
 * @property numeric $total_staking_amount
 * @property numeric $total_withdraw_amount
 * @property numeric $total_income
 * @property numeric $total_actual_income
 * @property numeric $total_loyalty_value
 * @property numeric $total_today_loyalty_value
 * @property integer $referral_count
 * @property date $first_staking_time
 * @property integer $leverage
 * @property integer $duration
 * @property boolean $is_cool_user
 * @property integer $today_had_help_count
 * @property date $show_card_at
 * @property date $trailed_at
 * @property string $status
 * @property date $last_login_at
 * @property string $username
 * @property string $password
 * @property date $created_at
 * @property date $updated_at
 * @property boolean $membership_card
 * @property boolean $first_withdrawal_free
 * @property date $first_withdrawal_free_date
 * @property date $membership_start_date
 * @property date $membership_end_date

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
class BaseUsers extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'users';
    protected string $comment = '';
    protected $fillable = ['platform', 'address', 'invite_code', 'vips_id', 'referral_url', 'parent_1_id', 'parent_2_id', 'parent_3_id', 'email', 'email_verified_at', 'avatar', 'nickname', 'bio', 'phone_number', 'facebook', 'telegram', 'wechat', 'skype', 'whatsapp', 'line', 'zalo', 'profile_verified_at', 'profile_status', 'profile_error_message', 'profile_error_last_at', 'profile_error_count_today', 'full_name', 'id_no', 'country', 'city', 'id_front_img', 'id_reverse_img', 'self_photo_img', 'identity_verified_at', 'identity_status', 'identity_error_message', 'identity_error_last_at', 'identity_error_count_today', 'can_automatic_trade', 'can_trail_bonus', 'can_automatic_exchange', 'can_email_notification', 'can_leveraged_investment', 'can_automatic_loan_repayment', 'can_prevent_liquidation', 'prevent_liquidation_amount', 'can_profit_guarantee', 'can_automatic_airdrop_bonus', 'can_automatic_staking', 'staking_type', 'can_automatic_withdrawal', 'automatic_withdrawal_amount', 'can_say', 'staking', 'withdrawable', 'total_balance', 'total_rate', 'total_staking_amount', 'total_withdraw_amount', 'total_income', 'total_actual_income', 'total_loyalty_value', 'total_today_loyalty_value', 'referral_count', 'first_staking_time', 'leverage', 'duration', 'is_cool_user', 'today_had_help_count', 'show_card_at', 'trailed_at', 'status', 'last_login_at', 'username', 'password', 'membership_card', 'first_withdrawal_free', 'first_withdrawal_free_date', 'membership_start_date', 'membership_end_date'];


    # region relations
    public function vip(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Vips::class, 'vips_id', 'id');
    }
    public function asset_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\AssetLogs::class, 'users_id', 'id');
    }
    public function assets(): Relations\HasMany
    {
        return $this->hasMany(Models\Assets::class, 'users_id', 'id');
    }
    public function case_details(): Relations\HasMany
    {
        return $this->hasMany(Models\CaseDetails::class, 'users_id', 'id');
    }
    public function cases(): Relations\HasMany
    {
        return $this->hasMany(Models\Cases::class, 'users_id', 'id');
    }
    public function fake_users(): Relations\HasMany
    {
        return $this->hasMany(Models\FakeUsers::class, 'users_id', 'id');
    }
    public function gifts(): Relations\HasMany
    {
        return $this->hasMany(Models\Gifts::class, 'users_id', 'id');
    }
    public function jackpot_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotLogs::class, 'users_id', 'id');
    }
    public function jackpots_has_users(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotsHasUsers::class, 'users_id', 'id');
    }
    public function pledge_profits(): Relations\HasMany
    {
        return $this->hasMany(Models\PledgeProfits::class, 'users_id', 'id');
    }
    public function pledges(): Relations\HasMany
    {
        return $this->hasMany(Models\Pledges::class, 'users_id', 'id');
    }
    public function pledges_has_funds(): Relations\HasMany
    {
        return $this->hasMany(Models\PledgesHasFunds::class, 'users_id', 'id');
    }
    public function sys_messages(): Relations\HasMany
    {
        return $this->hasMany(Models\SysMessages::class, 'users_id', 'id');
    }
    public function user_balance_snapshots(): Relations\HasMany
    {
        return $this->hasMany(Models\UserBalanceSnapshots::class, 'users_id', 'id');
    }
    public function user_earning_snapshots(): Relations\HasMany
    {
        return $this->hasMany(Models\UserEarningSnapshots::class, 'users_id', 'id');
    }
    public function user_follows(): Relations\HasMany
    {
        return $this->hasMany(Models\UserFollows::class, 'users_id', 'id');
    }
    public function web3_transactions(): Relations\HasMany
    {
        return $this->hasMany(Models\Web3Transactions::class, 'users_id', 'id');
    }

    # endregion
}
