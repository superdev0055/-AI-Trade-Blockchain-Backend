<?php


namespace App\Modules\Admin;


use App\Models\Vips;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class VipsController
 * @package App\Modules\Admin
 */
class VipsController extends AdminBaseController
{
    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function list(Request $request): mixed
    {
        $params = $request->validate([
            'name' => 'nullable|string', # 模糊搜索：名称
        ]);
        return Vips::ifWhereLike($params, 'name')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 修改
     * @param Request $request
     * @return array
     */
    public function update(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
            'name' => 'required|string', # 名称
            'need_stake' => 'required|numeric', #
            'can_automatic_trade' => 'required|boolean', #
            'can_trail_bonus' => 'required|boolean', #
            'can_automatic_exchange' => 'required|boolean', #
            'can_email_notification' => 'required|boolean', #
            'can_leveraged_investment' => 'required|boolean', #
            'can_automatic_loan_repayment' => 'required|boolean', #
            'can_prevent_liquidation' => 'required|boolean', #
            'can_profit_guarantee' => 'required|boolean', #
            'can_automatic_airdrop_bonus' => 'required|boolean', #
            'can_automatic_staking' => 'required|boolean', #
            'can_automatic_withdrawal' => 'required|boolean', #
            'daily_referral_rewards' => 'required|integer', #
            'level_1_refer' => 'required|numeric', #
            'level_2_refer' => 'required|numeric', #
            'level_3_refer' => 'required|numeric', #
            'can_pm_friends' => 'required|boolean', #
            'can_customize_online_status' => 'required|boolean', #
            'can_view_contact_details' => 'required|boolean', #
            'can_send_gift' => 'required|boolean', #
            'leveraged_investment' => 'required|integer', #
            'loan_charges' => 'required|numeric', #
            'minimum_apy_guarantee' => 'required|numeric', #
            'can_promotion_first_notice' => 'required|boolean', #
            'can_exclusive_customer_service' => 'required|boolean', #
            'max_staking_term' => 'required|integer', #
            'minimum_withdrawal_limit' => 'required|numeric', #
            'maximum_withdrawal_limit' => 'required|numeric', #
            'number_of_withdrawals' => 'required|integer', #
            'withdrawal_time' => 'required|integer', #
            'network_fee' => 'required|numeric', #
            'need_withdrawal_verification' => 'required|boolean', #
        ]);
        Vips::unique($params, ['name'], '名称');
        Vips::idp($params)->update($params);
        return [];
    }

    /**
     * @intro 选择
     * @return mixed
     */
    public function select(): mixed
    {
        return Vips::selectRaw('id as value,name as label')->orderBy('id', 'asc')->get();
    }
}
