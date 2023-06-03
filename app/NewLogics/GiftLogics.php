<?php

namespace App\NewLogics;

use App\Enums\GiftStatusEnum;
use App\Models\GiftDetails;
use App\Models\Users;
use App\NewServices\AssetsServices;
use App\NewServices\CoinServices;
use App\NewServices\GiftsServices;
use App\NewServices\PledgesServices;
use App\NewServices\VipsServices;
use Exception;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class GiftLogics
{
    /**
     * @param Users $user
     * @param string $type
     * @param float $amount
     * @param int $totalCount
     * @return void
     * @throws Exception
     */
    public static function SendGift(Users $user, string $type, float $amount, int $totalCount): void
    {
        CommonHelper::Trans(function () use ($user, $type, $amount, $totalCount) {
            $vip = VipsServices::GetByUser($user);
            if (!$vip->can_send_gift)
                Err::Throw(__("You can not send gift, please upgrade your vip level"));

            $pledge = PledgesServices::GetByUser($user);
            if ($pledge && $pledge->is_trail)
                Err::Throw(__("Your can not send gift when you are in trailing"));

            $assets = AssetsServices::getOrCreateAirdropAsset($user);

            // 判断余额
            if ($assets->balance < $amount)
                Err::Throw(__("Your airdrop balance is not enough"));

            // 创建gift
            GiftsServices::Create($user, $type, $amount, $totalCount);

            // 减余额
            $assets->balance -= $amount;
            $assets->save();
        });
    }

    /**
     * @param Users $user
     * @param int $giftId
     * @return array
     * @throws Exception
     */
    public static function ReceiveGift(Users $user, int $giftId): array
    {
        CommonHelper::Trans(function () use ($user, $giftId, &$detail) {
            $pledge = PledgesServices::GetByUser($user);
            if ($pledge && $pledge->is_trail)
                Err::Throw(__("Your can not send gift when you are in trailing"));

            // lock
            $gift = GiftsServices::GetById($giftId, lock: true);

            // status
            if ($gift->status != GiftStatusEnum::OnGoing->name)
                Err::Throw(__("There is no quota in the gift"));

            // count
            if ($gift->received_count == $gift->total_count)
                Err::Throw(__("There is no quota in the gift"));

            // detail exists
            $exists = GiftDetails::where('to_users_id', $user->id)->where('gifts_id', $giftId)->exists();
            if ($exists)
                Err::Throw(__("You had received this gift"));

            // amount
            $formula = json_decode($gift->formula, true);
            $receivedAmount = $formula['list'][$gift->received_count]['amount'];

            // update gift
            $gift->received_count += 1;
            if ($gift->received_count == $gift->total_count)
                $gift->status = GiftStatusEnum::Finished->name;
            $gift->save();

            // create gift details
            $detail = GiftDetails::create([
                'gifts_id' => $gift->id, #
                'from_users_id' => $gift->users_id, #
                'to_users_id' => $user->id, #
                'amount' => $receivedAmount, #
            ]);

            // add to user's assets
            $assets = AssetsServices::getOrCreateAirdropAsset($user);
            $assets->balance += $receivedAmount;
            $assets->save();

            SysMessageLogics::GiftReceived($user, $detail, CoinServices::GetUSDC());
        });

        return GiftDetails::with(['gift' => function ($query) {
            $query->withUser();
        }])
            ->findOrFail($detail->id)
            ->toArray();
    }
}
