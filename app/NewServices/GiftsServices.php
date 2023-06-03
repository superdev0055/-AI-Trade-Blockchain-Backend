<?php

namespace App\NewServices;

use App\Enums\GiftTypeEnum;
use App\Models\Gifts;
use App\Models\Users;
use App\NewLogics\CommonLogics;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class GiftsServices
{
    /**
     * @param Users $user
     * @param string $type
     * @param float $amount
     * @param int $totalCount
     * @return Gifts
     * @throws Err
     */
    public static function Create(Users $user, string $type, float $amount, int $totalCount): Gifts
    {
        $config = ConfigsServices::Get('gift');

        // 计算公式
        $formula = self::GetFormula($type, $amount, $totalCount);

        $gift = Gifts::create([
            'users_id' => $user->id, #
            'amount' => $amount, #
            'type' => $type, # 类型:RandomAmount,FixedAmount
            'total_count' => $totalCount, # 总数量
//            'received_count' => '', # 已领取数量
            'fee' => $config['fee'], # 手续费
//            'fee_amount' => '', # 手续费
//            'status' => '', # 状态:OnGoing,Finished
            'formula' => json_encode($formula), # 计算公式
        ]);

        $gift->code = CommonLogics::GetHashCode('gift', $gift->id);
        $gift->save();

        return $gift;
    }

    /**
     * @param int $id
     * @param bool $throw
     * @param bool $lock
     * @return Gifts
     * @throws Err
     */
    public static function GetById(int $id, bool $throw = true, bool $lock = false): Gifts
    {
        $gift = $lock ? Gifts::lockForUpdate()->find($id) : Gifts::find($id);
        if (!$gift && $throw)
            Err::Throw(__("The gift is not found"));
        return $gift;
    }

    /**
     * @param string $type
     * @param float $totalAmount
     * @param int $totalCount
     * @return array
     */
    #[ArrayShape(['list' => "array", 'totalCount' => "int", 'totalSeed' => "int", 'totalAmount' => "float"])]
    public static function GetFormula(string $type, float $totalAmount, int $totalCount): array
    {
        $seeds = [];
        $totalSeed = 0;

        foreach (range(1, $totalCount) as $i) {
            $value = $type == GiftTypeEnum::FixedAmount->name ? 1 : rand(1, 100);
            $seeds[] = $value;
            $totalSeed += $value;
        }

        $list = [];
        $sendAmount = 0;
        foreach ($seeds as $i => $seed) {
            if ($i + 1 == $totalCount) {
                $amount = $totalAmount - $sendAmount;
            } else {
                $amount = round($totalAmount * $seed / $totalSeed, 2);
            }
            $sendAmount += $amount;
            $list[] = [
                'seed' => $seed,
                'amount' => $amount,
            ];
        }
        return [
            'list' => $list,
            'totalCount' => $totalCount,
            'totalSeed' => $totalSeed,
            'totalAmount' => $totalAmount,
        ];
    }
}
