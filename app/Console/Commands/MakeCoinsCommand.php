<?php

namespace App\Console\Commands;

use App\Helpers\CoinGecko\CGProHelper;
use App\Models\Coins;
use Exception;
use Illuminate\Console\Command;

class MakeCoinsCommand extends Command
{
    protected $signature = 'MakeCoinsCommand';
    protected $description = 'Command description';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $res = CGProHelper::GetTop100Coins();

        foreach ($res as $item) {
            // 处理coin
            dump("Coins::updateOrInsert:::{$item['id']}");
            Coins::updateOrInsert(
                [
                    'cg_id' => $item['id']
                ],
                [
                    'symbol' => $item['symbol'], #
                    'name' => $item['name'], #
                    'icon' => $item['image'], #
                    'market_cap_rank' => $item['market_cap_rank'], #
                    'sparkline' => json_encode($item['sparkline_in_7d']['price']), #
                ]
            );
        }
        return 0;
    }
}
