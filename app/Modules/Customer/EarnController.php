<?php

namespace App\Modules\Customer;

use App\Enums\CacheTagsEnum;
use App\Enums\JackpotsStatusEnum;
use App\Models\Funds;
use App\Models\Jackpots;
use App\Models\JackpotsHasUsers;
use App\Modules\CustomerBaseController;
use App\NewServices\FriendsServices;
use App\NewServices\JackpotsServices;
use App\NewServices\UserEarningSnapshotsServices;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro Earn页面接口
 */
class EarnController extends CustomerBaseController
{
    /**
     * @intro Earn页面
     * @return array[]
     * @throws Err
     */
    #[ArrayShape(['your_yield' => "array", 'funds' => "array", 'jackpot' => "array"])]
    public function show(): array
    {
        $user = $this->getUser();

        $jackpotNow = JackpotsServices::Get();

        $jackpotNowUsers = JackpotsHasUsers::with('user:id,nickname,address,avatar,vips_id,total_income,total_loyalty_value')
            ->whereHas('user', function ($query) {
                $query->where('can_automatic_airdrop_bonus', true);
            })
            ->where('airdrop', '>', 0)
            ->where('jackpots_id', $jackpotNow->id)
            ->orderByDesc('loyalty')
            ->take(50)
            ->get()
            ->toArray();
        $this->processUsers($jackpotNowUsers);

        $jackpotPrev = Jackpots::where('status', JackpotsStatusEnum::Finished->name)
            ->where('id', '<', $jackpotNow->id)
            ->orderByDesc('id')
            ->first();

        $jackpotPrevUsers = !$jackpotPrev ? null : JackpotsHasUsers::with('user:id,nickname,address,avatar,vips_id,total_income,total_loyalty_value')
            ->where('airdrop', '>', 0)
            ->where('jackpots_id', $jackpotPrev->id)
            ->orderByDesc('loyalty')
            ->take(50)
            ->get()
            ->toArray();
        $this->processUsers($jackpotPrevUsers);

        return [
            'your_yield' => [
                'amount' => $user->total_actual_income,
                'rate' => $user->total_rate,
                'direction' => $user->total_rate >= 0 ? 'up' : 'down',
                'balance_snapshot' => UserEarningSnapshotsServices::GetTop200EarningSparkline($user)
            ],
            'funds' => [
                'card' => Funds::query()
                    ->with('mainCoin')
                    ->with('subCoin')
                    ->inRandomOrder()
                    ->take(10)
                    ->where('sub_coins_id', null)
                    ->get()
                    ->toArray(),
                'list' => Funds::query()
                    ->with('mainCoin')
                    ->with('subCoin')
                    ->inRandomOrder()
                    ->where('sub_coins_id', '!=', 'null')
                    ->take(20)
                    ->get()
                    ->toArray(),
            ],
            'jackpot' => [
                'now' => [
                    'started_at' => $jackpotNow->started_at,
                    'goal' => $jackpotNow->goal,
                    'balance' => $jackpotNow->balance,
                    'users' => $jackpotNowUsers
                ],
                'prev' => !$jackpotPrev ? null : [
                    'started_at' => $jackpotPrev->started_at,
                    'goal' => $jackpotPrev->goal,
                    'balance' => $jackpotPrev->balance,
                    'users' => $jackpotPrevUsers
                ]
            ]
        ];
    }

    /**
     * @param array|null $jackpotsHasUsers
     * @return void
     * @throws Err
     */
    private function processUsers(?array &$jackpotsHasUsers): void
    {
        if (!$jackpotsHasUsers) {
            return;
        }

        $me = $this->getUser();
        for ($i = 0; $i < count($jackpotsHasUsers); $i++) {
            $address = $jackpotsHasUsers[$i]['user']['address'];
            $jackpotsHasUsers[$i]['user']['online_status'] = Cache::tags([CacheTagsEnum::OnlineStatus->name])->get($jackpotsHasUsers[$i]['user']['id'], false);
            $jackpotsHasUsers[$i]['user']['follow_status'] = FriendsServices::GetFollowStatus($me, $jackpotsHasUsers[$i]['user']['id']);
            $jackpotsHasUsers[$i]['user']['address'] = substr($address, 0, 6) . '...' . substr($address, -4);
        }
    }
}
