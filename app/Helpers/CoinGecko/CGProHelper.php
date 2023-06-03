<?php

namespace App\Helpers\CoinGecko;

use App\Enums\CacheTagsEnum;
use App\Models\Coins;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class CGProHelper
{
    /**
     * 获取前100的coin
     * @return mixed
     * @throws Exception
     */
    public static function GetTop100Coins(): mixed
    {
        $api = new CGProApi();
        return $api->coinsMarkets('usd', null, true);
    }

    /**
     * 从缓存获取单个coin的市场信息
     * @param Coins $coin
     * @return mixed|null
     * @throws Exception
     */
    public static function GetCoinsMarkets(Coins $coin): mixed
    {
        return Cache::tags([CacheTagsEnum::GetCoinsMarkets->name])->remember($coin->cg_id, 0.5 * 60 * 60, function () use ($coin) {
            return self::getCoinMarkets($coin);
        });
    }

    /**
     * 设置所有coin的市场信息到缓存
     * @return void
     * @throws Exception
     */
    public static function SetAllCoinsMarketsToCache(): void
    {
        Coins::each(function (Coins $coin) {
            Cache::tags([CacheTagsEnum::GetCoinsMarkets->name])->forever($coin->cg_id, self::getCoinMarkets($coin));
            sleep(2);
        });
    }

    /**
     * 获取单个coin的市场信息
     * @param Coins $coin
     * @param string $currency
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['info' => "mixed", 'data' => "array"])]
    private static function getCoinMarkets(Coins $coin, string $currency = 'USD'): array
    {
        Log::debug('Start Call GetCoinsMarkets:::');
        $api = new CGProApi();
        $info = $api->coins($coin->cg_id);
        $day = $api->coinsMarketChart($coin->cg_id, $currency, '1')['prices'];
        $week = $api->coinsMarketChart($coin->cg_id, $currency, '7')['prices'];
        $month = $api->coinsMarketChart($coin->cg_id, $currency, '30')['prices'];
        $year = $api->coinsMarketChart($coin->cg_id, $currency, '365')['prices'];
        $all = $api->coinsMarketChart($coin->cg_id, $currency, 'max')['prices'];
        Log::debug('End Call GetCoinsMarkets:::');

        $coin->sparkline = json_encode(array_column($week, 1));
        $coin->save();

        return [
            'info' => $info,
            'data' => [
                '1D' => $day,
                '1W' => $week,
                '1M' => $month,
                '1Y' => $year,
                'ALL' => $all,
            ]
        ];
    }

    /**
     * @ok
     * 获取coin价格
     * @param $cgId
     * @return float
     * @throws Exception
     */
    public static function GetCoinsPrice($cgId): float
    {
        return Cache::tags([CacheTagsEnum::GetCoinsPrice->name])->remember($cgId, 0.5 * 60 * 60, function () use ($cgId) {
            $api = new CGProApi();
            return $api->simplePrice($cgId)[$cgId]['usd'];
        });
    }

    /**
     * 设置所有coin的价格到缓存
     * @return void
     * @throws Exception
     */
    public static function SetAllCoinsPriceToCache(): void
    {
        $api = new CGProApi();
        $ids = implode(",", Coins::selectRaw('id,cg_id')->get()->pluck('cg_id')->toArray());
        $res = $api->simplePrice($ids);
        foreach ($res as $key => $value) {
            Cache::tags([CacheTagsEnum::GetCoinsPrice->name])->forever($key, $value['usd']);
        }
    }
}
