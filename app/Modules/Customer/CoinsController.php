<?php

namespace App\Modules\Customer;

use App\Helpers\CoinGecko\CGProHelper;
use App\Modules\CustomerBaseController;
use App\NewServices\CoinServices;
use Exception;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class CoinsController extends CustomerBaseController
{
    /**
     * @intro 获取coin详情
     * @param Request $request
     * @return mixed|null
     * @throws Exception
     */
    public function info(Request $request): mixed
    {
        $params = $request->validate([
            'symbol' => 'required|string' # symbol
        ]);
        $coin = CoinServices::GetCoin($params['symbol']);
        return CGProHelper::GetCoinsMarkets($coin);
    }

//    /**
//     * @param Request $request
//     * @return mixed
//     * @throws CMCApiResponseException
//     * @throws BindingResolutionException
//     */
//    public function info(Request $request): mixed
//    {
//        $params = $request->validate([
//            'symbol' => 'required|string' # symbol
//        ]);
//        return CoinServices::GetCoinInfo($params['symbol']);
//    }
//
//    /**
//     * @intro 曲线图
//     * @param Request $request
//     */
//    public function sparkline(Request $request)
//    {
//        $params = $request->validate([
//            'symbol' => 'required|string',
//            'interval' => 'string|integer', #  1d,7d,30d,90d,365d
//        ]);
//    }
//
    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['price' => "float"])]
    public function getPrice(Request $request): array
    {
        $params = $request->validate([
            'symbol' => 'required|string',
        ]);
        $coin = CoinServices::GetCoin($params['symbol']);
        return [
            'price' => CGProHelper::GetCoinsPrice($coin->cg_id)
        ];
    }
}
