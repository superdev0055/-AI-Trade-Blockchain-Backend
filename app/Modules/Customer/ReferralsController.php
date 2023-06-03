<?php

namespace App\Modules\Customer;

use App\Mail\InviteMail;
use App\Models\Users;
use App\Modules\CustomerBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use LaravelCommon\App\Exceptions\Err;

class ReferralsController extends CustomerBaseController
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
        return Users::whereParentUser($user)
            ->when($params['Level'] ?? false, function ($query) use ($params, $user) {
                switch ($params['Level']) {
                    case 1:
                        $query->where('parent_1_id', $user->id);
                        break;
                    case 2:
                        $query->where('parent_2_id', $user->id);
                        break;
                    case 3:
                        $query->where('parent_3_id', $user->id);
                        break;
                    default:
                        break;
                }
            })
            ->ifWhereLike($params, 'nickname')
            ->ifWhereLike($params, 'address')
            ->ifRange($params, 'created_at')
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
        $query = Users::whereParentUser($user);
        return [
            (clone $query)->count(),
            (clone $query)->where('total_staking_amount', '>', 0)->count(),
            (clone $query)->where('total_withdraw_amount', '>', 0)->count(),
            (clone $query)->where('total_staking_amount', '>', 0)->count(),
            (clone $query)->where('total_staking_amount', '=', 0)->count(),
            (clone $query)->sum('total_staking_amount'),
            (clone $query)->sum('total_income'),
        ];
    }

    /**
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function invite(Request $request): void
    {
        $params = $request->validate([
            'email' => 'required|email', # email
        ]);
        $user = $this->getUser();
        Mail::to($params['email'])->send(new InviteMail($user));
    }
}
