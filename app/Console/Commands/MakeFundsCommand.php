<?php

namespace App\Console\Commands;

use App\Enums\FundsProductTypeEnum;
use App\Enums\FundsRiskTypeEnum;
use App\Models\Coins;
use App\Models\Funds;
use App\NewServices\ConfigsServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Exceptions\Err;

class MakeFundsCommand extends Command
{
    protected $signature = 'MakeFundsCommand';
    protected $description = 'Command description';
    protected array $subCoinSymbols = ['usdc', 'usdt', 'eth', 'btc', 'dai'];

    /**
     * @return int
     * @throws Err
     */
    public function handle(): int
    {
//        dd(json_decode(Funds::first()->profits, true)[360]);

        // Delete all
        Funds::query()->delete();

        // 生成Earn
        $list = DB::table('coins')->inRandomOrder()->take(50)->get();
        $list->each(function ($coin) {
            $profit = $this->getProfit();
            Funds::create([
                'product_type' => FundsProductTypeEnum::Earn->name, #
                'risk_type' => FundsRiskTypeEnum::Protected->name, #
                'name' => $coin->symbol, #
                'main_coins_id' => $coin->id, #
//                'sub_coins_id' => '', # ref[Coins]
                'profits' => json_encode($profit),
                'duration' => 7,
                'apr_start' => $profit[7]['apr_start'],
                'apr_end' => $profit[7]['apr_end']
            ]);
            $min = $profit[7]['apr_start'] * 100 . '%';
            $max = $profit[7]['apr_end'] * 100 . '%';
            dump("Earn:::{$coin->name}:::$min:::$max");
        });

        // 生成 Defi Staking
        $list = DB::table('coins')->inRandomOrder()->take(50)->get();
        $list->each(function ($coin) {
            $profit = $this->getProfit();
            Funds::create([
                'product_type' => FundsProductTypeEnum::DEFIStaking->name, #
                'risk_type' => FundsRiskTypeEnum::Protected->name, #
                'name' => $coin->symbol, #
                'main_coins_id' => $coin->id, #
//                'sub_coins_id' => '', #
                'profits' => json_encode($profit),
                'duration' => 7,
                'apr_start' => $profit[7]['apr_start'],
                'apr_end' => $profit[7]['apr_end']
            ]);
            $min = $profit[7]['apr_start'] * 100 . '%';
            $max = $profit[7]['apr_end'] * 100 . '%';
            dump("Defi Staking:::{$coin->name}:::$min:::$max");
        });

        // 生成 Liquidity
        $list = DB::table('coins')->inRandomOrder()->take(50)->get();
        $list->each(function ($coin) {
            $profit = $this->getProfit();
            $subCoin = $this->getSubCoin();
            Funds::create([
                'product_type' => FundsProductTypeEnum::Liquidity->name, #
                'risk_type' => FundsRiskTypeEnum::HighYield->name, #
                'name' => $coin->symbol . "/" . $subCoin->symbol, #
                'main_coins_id' => $coin->id, #
                'sub_coins_id' => $subCoin->id, #
                'profits' => json_encode($profit),
                'duration' => 7,
                'apr_start' => $profit[7]['apr_start'],
                'apr_end' => $profit[7]['apr_end']
            ]);
            $min = $profit[7]['apr_start'] * 100 . '%';
            $max = $profit[7]['apr_end'] * 100 . '%';
            dump("Liquidity:::{$coin->name}:::$min:::$max");
        });

        // 生成 Swap
        $list = DB::table('coins')->inRandomOrder()->take(50)->get();
        $list->each(function ($coin) {
            $profit = $this->getProfit();
            $subCoin = $this->getSubCoin();
            Funds::create([
                'product_type' => FundsProductTypeEnum::Swap->name, #
                'risk_type' => FundsRiskTypeEnum::HighYield->name, #
                'name' => $coin->symbol . "/" . $subCoin->symbol, #
                'main_coins_id' => $coin->id, #
                'sub_coins_id' => $subCoin->id, #
                'profits' => json_encode($profit),
                'duration' => 7,
                'apr_start' => $profit[7]['apr_start'],
                'apr_end' => $profit[7]['apr_end']
            ]);
            $min = $profit[7]['apr_start'] * 100 . '%';
            $max = $profit[7]['apr_end'] * 100 . '%';
            dump("Swap:::{$coin->symbol}:::$min:::$max");
        });
        return 0;
    }

    private function getSubCoin()
    {
        $symbol = $this->subCoinSymbols[array_rand($this->subCoinSymbols)];
        return Coins::where('symbol', $symbol)->firstOrFail();
    }

    /**
     * @return array
     * @throws Err
     */
    private function getProfit(): array
    {
        $newProfit = [];
        $profit = ConfigsServices::Get('profit');
        foreach ($profit as $key => $value) {
            $start = $value['apr_start'] * 100;
            $end = $value['apr_end'] * 100;

            $apr1 = floatval(rand($start, $end / 2) / 100);
            $apr2 = floatval(rand($end / 2, $end) / 100);

            $newProfit[$key] = [
                'apr_start' => min([$apr1, $apr2]),
                'apr_end' => max([$apr1, $apr2]),
            ];
        }
        return $newProfit;
    }
}
