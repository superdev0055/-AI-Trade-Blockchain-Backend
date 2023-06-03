<?php

namespace App\Helpers\CoinGecko;

use Exception;
use Illuminate\Support\Facades\Http;
use LaravelCommon\App\Helpers\CommonHelper;

class CGProApi
{
    private string $baseUrl;
    private string $apiKey;
    private array $options;
    private bool $isPro;

    public function __construct()
    {
        $this->isPro = config('web3.cgProApiKey') != '';
        if ($this->isPro) {
            $this->baseUrl = 'https://pro-api.coingecko.com/api/v3/';
            $this->apiKey = config('web3.cgProApiKey');
        } else {
            $this->baseUrl = 'https://api.coingecko.com/api/v3/';
            $this->apiKey = '';
        }
        $this->options = [
            'proxy' => CommonHelper::GetProxies()
        ];
    }

    /**
     * @param string $ids
     * @param string $vs_currencies
     * @return mixed
     * @throws Exception
     */
    public function simplePrice(string $ids, string $vs_currencies = 'usd'): mixed
    {
        return $this->doRequest('/simple/price', [
            'ids' => $ids,
            'vs_currencies' => $vs_currencies,
        ]);
    }

    /**
     * @param string $currency
     * @param string|null $ids
     * @param bool $sparkline
     * @return mixed
     * @throws Exception
     */
    public function coinsMarkets(string $currency = 'usd', ?string $ids = null, bool $sparkline = false): mixed
    {
        $params = [
            'vs_currency' => $currency,
            'order' => 'market_cap_desc',
            'per_page' => 100,
            'page' => 1,
        ];

        if ($ids)
            $params['ids'] = $ids;

        if ($sparkline)
            $params['sparkline'] = 'true';

        return $this->doRequest('/coins/markets', $params);
    }

    /**
     * @param string $cgId
     * @return mixed
     * @throws Exception
     */
    public function coins(string $cgId): mixed
    {
        $params = [];
        return $this->doRequest("/coins/$cgId", $params);
    }


    /**
     * @param string $cgID
     * @param string $currency
     * @param string $days
     * @param string $interval
     * @return mixed
     * @throws Exception
     */
    public function coinsMarketChart(string $cgID = 'ethereum', string $currency = 'usd', string $days = '7', string $interval = 'daily'): mixed
    {
        return $this->doRequest("/coins/$cgID/market_chart", [
            'vs_currency' => $currency,
            'days' => $days,
//            'interval' => $interval,
        ]);
    }

    /**
     * @param string $url
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    private function doRequest(string $url, array $params): mixed
    {
        if ($this->isPro)
            $params['x_cg_pro_api_key'] = $this->apiKey;
        $res = Http::withOptions($this->options)
            ->get($this->baseUrl . $url, $params)
            ->json();

//        if ($res['status']['error_message'] != null)
//            throw new Exception($res['status']['error_message']);

        return $res;
    }
}
