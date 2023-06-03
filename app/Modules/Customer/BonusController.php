<?php

namespace App\Modules\Customer;

use App\Enums\UserBonusesStatusEnum;
use App\Enums\UserBonusesTypeEnum;
use App\Models\Bonuses;
use App\Modules\CustomerBaseController;
use App\NewServices\BonusesServices;
use Exception;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

class BonusController extends CustomerBaseController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function list(Request $request): mixed
    {
        $params = $request->validate([
            'nickname' => 'nullable|string', # nickname
            'address' => 'nullable|string', # address
            'Level' => 'nullable|integer', # Level
            'created_at' => 'nullable|array', # 时间范围：[from,to]
        ]);
        $user = $this->getUser();
        return Bonuses::ifRange($params, 'created_at')
            ->with('from')
            ->where('to_users_id', $user->id)
            ->where('bonus', '>', 0.000001)
            ->when(isset($params['nickname']), function ($q) use ($params) {
                $q->whereHas('from', function ($q1) use ($params) {
                    $q1->ifWhereLike($params, 'nickname');
                });
            })
            ->when($params['Level'] ?? false, function ($q) use ($params, $user) {
                switch ($params['Level']) {
                    case 1:
                        $q->whereHas('from', function ($q1) use ($params, $user) {
                            $q1->where('parent_1_id', $user->id);
                        });
                        break;
                    case 2:
                        $q->whereHas('from', function ($q1) use ($params, $user) {
                            $q1->where('parent_2_id', $user->id);
                        });
                        break;
                    case 3:
                        $q->whereHas('from', function ($q1) use ($params, $user) {
                            $q1->where('parent_3_id', $user->id);
                        });
                        break;
                    default:
                        break;
                }
            })
            ->when(isset($params['address']), function ($q) use ($params) {
                $q->whereHas('from', function ($q1) use ($params) {
                    $q1->ifWhereLike($params, 'address');
                });
            })
            ->descID()
            ->paginate($this->perPage());
    }

    /**
     * @return array
     * @throws Err
     */
    public function statistics(): array
    {
        $user = $this->getUser();
        $query = Bonuses::where('to_users_id', $user->id);

        $firstBonus = (clone $query)->orderBy('id')->first();

        return [
            (clone $query)->sum('bonus'), // Team bonus

            (clone $query)->where('type', UserBonusesTypeEnum::PledgeProfit->name)
                ->whereHas('from', function ($q) use ($user) {
                    $q->where('parent_1_id', $user->id);
                })->sum('bonus'), // Level 1 bonus

            (clone $query)->where('type', UserBonusesTypeEnum::PledgeProfit->name)
                ->whereHas('from', function ($q) use ($user) {
                    $q->where('parent_2_id', $user->id);
                })->sum('bonus'), // Level 2 bonus

            (clone $query)->where('type', UserBonusesTypeEnum::PledgeProfit->name)
                ->whereHas('from', function ($q) use ($user) {
                    $q->where('parent_3_id', $user->id);
                })->sum('bonus'), // Level 3 bonus

            (clone $query)->where('type', UserBonusesTypeEnum::Referral->name)->sum('bonus'), // Referral bonus

            (clone $query)->where('status', UserBonusesStatusEnum::Waiting->name)->sum('bonus'), // Locked bonus

            0, // Pending bonus
//            (clone $query)->where('status', UserBonusesStatusEnum::Success->name)->sum('bonus'), // Success bonus

            $firstBonus ? $firstBonus->created_at : now()->toDateTimeString(),
            now()->toDateTimeString()
        ];
    }

    /**
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function unlock(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $user = $this->getUser();
        BonusesServices::UnlockBonus($user, $params['id']);
    }

    /**
     * @return void
     * @throws Err
     */
    public function unLockAll(): void
    {
        $user = $this->getUser();
        Bonuses::where('to_users_id', $user->id)
            ->where('status', UserBonusesStatusEnum::Waiting->name)
            ->each(function (Bonuses $item) use ($user) {
                BonusesServices::UnlockBonus($user, $item->id);
            });
    }
}
