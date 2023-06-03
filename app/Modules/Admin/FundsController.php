<?php


namespace App\Modules\Admin;


use App\Models\Funds;
use App\Modules\AdminBaseController;
use App\NewServices\CoinServices;
use App\NewServices\FundsServices;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class FundsController
 * @package App\Modules\Admin
 */
class FundsController extends AdminBaseController
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
            'product_type' => 'nullable|string', # :Earn,Liquidity,Swap,DualInvest,DEFIStaking
            'risk_type' => 'nullable|string', # :Protected,HighYield
        ]);
        return Funds::ifWhereLike($params, 'name')
            ->with('subCoin')
            ->with('mainCoin')
            ->ifWhere($params, 'product_type')
            ->ifWhere($params, 'risk_type')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 更新
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function update(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'product_type' => 'required|string', # :Earn,Liquidity,Swap,DualInvest,DEFIStaking
            'risk_type' => 'required|string', # :Protected,HighYield
            'main_coins_id' => 'required|integer', # ref[Coins]
            'sub_coins_id' => 'nullable|integer', # ref[Coins]
            'profits' => 'nullable|json', #
        ]);
        $funds = FundsServices::GetById($params['id']);
        $mainCoin = CoinServices::GetById($params['main_coins_id']);
        $params['name'] = $mainCoin->symbol;
        $profit = json_decode($params['profits'], true);
        $params['duration'] = 7;
        $params['apr_start'] = $profit["7"]['apr_start'];
        $params['apr_end'] = $profit["7"]['apr_end'];
        $funds->update($params);
    }

}
