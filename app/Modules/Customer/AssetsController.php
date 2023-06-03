<?php

namespace App\Modules\Customer;

use App\Models\AssetLogs;
use App\Modules\CustomerBaseController;
use App\NewLogics\Transfer\StakingLogics;
use App\NewServices\AssetsServices;
use App\NewServices\CoinServices;
use Exception;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class AssetsController extends CustomerBaseController
{
    /**
     * @return array
     * @throws Err
     */
    #[ArrayShape(['staking' => "mixed", 'withdrawable' => "mixed", 'pending' => "mixed"])]
    public function show(): array
    {
        $user = $this->getUser();
        return AssetsServices::getAllAsserts($user);
    }

    /**
     * @return array
     * @throws Err
     */
    #[ArrayShape(['header' => "array", 'data' => "array"])]
    public function chart(): array
    {
        $user = $this->getUser();
        return AssetsServices::getAllBalanceSparkline($user);
    }

    /**
     * @return array[]
     * @throws Err
     */
    public function statistics(): array
    {
        $user = $this->getUser();
        $usdc = CoinServices::GetUSDC();
        $staking = AssetsServices::getOrCreateStakingAsset($user, $usdc);
        $withdrawable = AssetsServices::getOrCreateWithdrawAsset($user, $usdc);
        return [
            ['id' => 1, 'title' => __('Total Balance'), 'icon' => $usdc->icon, 'symbol' => 'USDC', 'value' => $user->total_balance],
            ['id' => 2, 'title' => __('Total Staking'), 'icon' => $usdc->icon, 'symbol' => 'USDC', 'value' => $staking->balance],
            ['id' => 3, 'title' => __('Total Withdrawable'), 'icon' => $usdc->icon, 'symbol' => 'USDC', 'value' => $withdrawable->balance],
        ];
    }

    /**
     * @intro withdrawable里的Staking按钮
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function withdrawToStaking(Request $request): void
    {
        $params = $request->validate([
            'amount' => 'nullable|integer',
        ]);
        $user = $this->getUser();
        $withdrawable = AssetsServices::getOrCreateWithdrawAsset($user);
        StakingLogics::WithdrawableToStaking($user, $withdrawable, $params['amount'] ?? null);
    }

    /**
     * @return mixed
     * @throws Err
     */
    public function all(): mixed
    {
        $user = $this->getUser();
        return AssetLogs::who($user)
            ->order()
            ->paginate($this->perPage());
    }
}
