<?php

namespace App\Modules;

use App\Consts\Constants;
use App\NewServices\UsersServices;
use App\NewServices\VipsServices;
use Illuminate\Support\Facades\App;
use LaravelCommon\App\Exceptions\Err;
use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\Vips;

class CustomerBaseController extends Controller
{
    protected ?Users $user = null;
    protected ?Vips $vip = null;

    protected array $keys = [
        'can_automatic_trade',
        'can_trail_bonus',
        'can_automatic_exchange',
        'can_email_notification',
        'can_leveraged_investment',
        'can_automatic_loan_repayment',
        'can_prevent_liquidation',
        'can_profit_guarantee',
        'can_automatic_airdrop_bonus',
        'can_automatic_staking',
        'can_automatic_withdrawal',
    ];

    public function __construct()
    {
        $account = json_decode(request()->header('account'), true);
        $lang = $account['locale'] ?? 'en';
        $locale = Constants::LANGUAGES[$lang] ?? 'en';
        App::setLocale($locale);
    }

    /**
     * @ok
     * @return Users
     * @throws Err
     */
    protected function getUser(): Users
    {
        if (!$this->user) {
            $account = json_decode(request()->header('account'), true);
            if (!$account)
                Err::Throw(__("User not login"), 10000);
            $this->user = UsersServices::AutoRegisterUser($account);
        }
        return $this->user;
    }

    /**
     * @return Vips|null
     * @throws Err
     */
    protected function getVip(): ?Vips
    {
        if (!$this->vip) {
            $user = $this->getUser();
            $this->vip = VipsServices::GetByUser($user);
        }
        return $this->vip;
    }
}
