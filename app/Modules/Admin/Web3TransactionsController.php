<?php


namespace App\Modules\Admin;


use App\Enums\Web3TransactionsTypeEnum;
use App\Models\Web3Transactions;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class Web3TransactionsController
 * @package App\Modules\Admin
 */
class Web3TransactionsController extends AdminBaseController
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
            'from_address' => 'nullable|string', # from地址
            'to_address' => 'nullable|string', # to地址
            'coin_symbol' => 'nullable|string', #
            'type' => 'nullable|string', # :Staking,Withdraw,AutomaticWithdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking,DepositStaking,StakingRewardLoyalty
            'hash' => 'nullable|string', # hash
            'status' => 'nullable|string', # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
        return Web3Transactions::withUser()
            ->with('coin')
            ->ifWhereLike($params, 'from_address')
            ->ifWhereLike($params, 'to_address')
            ->ifWhereLike($params, 'coin_symbol')
            ->ifWhere($params, 'type')
            ->ifWhere($params, 'hash')
            ->ifWhere($params, 'status')
            ->ifWhereHasUserAddress($params)
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function loyaltyStaking(Request $request): mixed
    {
        $params = $request->validate([
            'coins_id' => 'nullable|integer', # 选择coin
            'from' => 'nullable|string', # 用户地址
            'hash' => 'nullable|string', # hash
            'sys_status' => 'nullable|boolean', # 业务状态：1:Waiting / 2:Failed / 3:Success
            'status' => 'nullable|boolean', # 区块链状态：1:Idle / 2:Error / 3:Loading / 4:Success
        ]);
        $params['type'] = Web3TransactionsTypeEnum::LoyaltyStaking->name;
        return Web3Transactions::ifWhere($params, 'coins_id')
            ->withUser()
            ->with('coin')
            ->ifWhereLike($params, 'from')
            ->ifWhereLike($params, 'hash')
            ->ifWhere($params, 'sys_status')
            ->ifWhere($params, 'status')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function approve(Request $request): mixed
    {
        $params = $request->validate([
            'coins_id' => 'nullable|integer', # 选择coin
            'from' => 'nullable|string', # 用户地址
            'hash' => 'nullable|string', # hash
            'sys_status' => 'nullable|boolean', # 业务状态：1:Waiting / 2:Failed / 3:Success
            'status' => 'nullable|boolean', # 区块链状态：1:Idle / 2:Error / 3:Loading / 4:Success
        ]);
        $params['type'] = Web3TransactionsTypeEnum::Approve->name;
        return Web3Transactions::ifWhere($params, 'coins_id')
            ->withUser()
            ->with('coin')
            ->ifWhereLike($params, 'from')
            ->ifWhereLike($params, 'hash')
            ->ifWhere($params, 'sys_status')
            ->ifWhere($params, 'status')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function withdraw(Request $request): mixed
    {
        $params = $request->validate([
            'coins_id' => 'nullable|integer', # 选择coin
            'input_address' => 'nullable|string', # 用户地址
            'hash' => 'nullable|string', # hash
            'sys_status' => 'nullable|boolean', # 业务状态：1:Waiting / 2:Failed / 3:Success
            'status' => 'nullable|boolean', # 区块链状态：1:Idle / 2:Error / 3:Loading / 4:Success
        ]);
        $params['type'] = Web3TransactionsTypeEnum::Withdraw->name;
        return Web3Transactions::ifWhere($params, 'coins_id')
            ->withUser()
            ->with('coin')
            ->ifWhereLike($params, 'from')
            ->ifWhereLike($params, 'hash')
            ->ifWhere($params, 'sys_status')
            ->ifWhere($params, 'status')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 查看
     * @param Request $request
     * @return array
     */
    public function show(Request $request): array
    {
        $params = $request->validate([
            'web3_transactions_id' => 'required|integer', # 选择coin
        ]);
        return Web3Transactions::withUser()
            ->with('coin')
            ->findOrFail($params['web3_transactions_id'])
            ->toArray();
    }
}
