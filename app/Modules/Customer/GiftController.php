<?php

namespace App\Modules\Customer;

use App\Enums\Web3TransactionsStatusEnum;
use App\Enums\Web3TransactionsTypeEnum;
use App\Models\GiftDetails;
use App\Models\Gifts;
use App\Models\Web3Transactions;
use App\Modules\CustomerBaseController;
use App\NewLogics\CommonLogics;
use App\NewLogics\GiftLogics;
use App\NewLogics\Transfer\ExchangeAirdropLogics;
use App\NewServices\AssetsServices;
use App\NewServices\CoinServices;
use App\NewServices\ConfigsServices;
use App\NewServices\GiftsServices;
use Exception;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class GiftController extends CustomerBaseController
{
    /**
     * @intro 在send之前调用
     * @return array
     * @throws Err
     * @throws Exception
     */
    #[ArrayShape(['min' => "mixed", 'fee' => "mixed", 'balance' => "float|int|string", 'usd_balance' => "float|int"])]
    public function preSend(): array
    {
        $user = $this->getUser();
        $balance = AssetsServices::getOrCreateAirdropAsset($user)->balance;
        $config = ConfigsServices::Get('gift');
        return [
            'min' => $config['min'],
            'fee' => $config['fee'],
            'balance' => $balance,
            'usd_balance' => round($balance * CoinServices::GetPrice('usdc'), 2)
        ];
    }

    /**
     * @intro 发送礼物
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function sendGift(Request $request): void
    {
        $params = $request->validate([
            'type' => 'required|string', # 类型:RandomAmount,FixedAmount
            'amount' => 'required|numeric', # 金额
            'total_count' => 'required|integer', # 总数量
        ]);
        $user = $this->getUser();
        GiftLogics::SendGift($user, $params['type'], $params['amount'], $params['total_count']);
    }

    /**
     * @intro 在接收礼物之前调用
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function preReceiveGift(Request $request): mixed
    {
        $params = $request->validate([
            'gift_code' => 'required|string', # gift分享出来的链接中的code
        ]);
        $giftId = CommonLogics::GetHashId('gift', $params['gift_code']);
        return Gifts::selectRaw('id,type,amount,total_count,received_count')->find($giftId);
    }

    /**
     * @intro 接收礼物
     * 1、生成url：https://xxx.com/customer/home/gift?gift_code=xxxx
     * 2、登录，并跳转到url
     * 3、检测到有gift_code参数，就弹窗
     * 4、弹窗里有一个按钮，点击领取，领取成功、失败，关闭弹窗，并刷新页面数据
     * @param Request $request
     * @return array
     * @throws Err
     * @throws Exception
     */
    public function receiveGift(Request $request): array
    {
        $params = $request->validate([
            'gift_code' => 'required|string', # gift分享出来的链接中的code
        ]);
        $user = $this->getUser();
        $giftId = CommonLogics::GetHashId('gift', $params['gift_code']);
        return GiftLogics::ReceiveGift($user, $giftId);
    }

    /**
     * @intro 发送过的礼物列表(分页)
     * @return mixed
     * @throws Err
     */
    public function sentGift(): mixed
    {
        $user = $this->getUser();
        return Gifts::who($user)->selectRaw('id,code,amount,total_count,received_count,created_at')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 收到过的礼物
     * @return mixed
     * @throws Err
     */
    public function receivedGift(): mixed
    {
        $user = $this->getUser();
        return GiftDetails::where('to_users_id', $user->id)
            ->withGift()
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 详情弹窗
     * @param Request $request
     * @return array
     * @throws Err
     */
    public function detail(Request $request): array
    {
        $params = $request->validate([
            'gift_code' => 'required|string', # gift分享出来的链接中的code
        ]);

        $user = $this->getUser();
        $giftId = CommonLogics::GetHashId('gift', $params['gift_code']);
        $gift = GiftsServices::GetById($giftId);
        if ($gift->users_id != $user->id)
            Err::Throw(__("The gift was not initiated by you"));
        return Gifts::with(['gift_details' => function ($query) {
            $query->with('from:id,nickname,avatar,vips_id');
        }])->findOrFail($giftId)
            ->toArray();
    }

    /**
     * @intro 点击兑换按钮，先调用
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function preExchange(Request $request): void
    {
        $params = $request->validate([
            'amount' => 'required|numeric' # 金额
        ]);
        $user = $this->getUser();
        $asset = AssetsServices::getOrCreateAirdropAsset($user);
        $config = ConfigsServices::Get('gift');
        ExchangeAirdropLogics::Can($user, $asset, $params['amount'], $config);
    }

    /**
     * @intro 兑换airdrop
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function exchange(Request $request): void
    {
        $params = $request->validate([
            'amount' => 'required|numeric', # 金额
            'hash' => 'required|string' # web3交易的hash值
        ]);
        $user = $this->getUser();
        ExchangeAirdropLogics::CreateOrder($user, $params['amount'], $params['hash']);
    }

    /**
     * @intro 已兑换列表（分页）
     * @return mixed
     * @throws Err
     */
    public function exchangedList(): mixed
    {
        $user = $this->getUser();
        return Web3Transactions::who($user)
            ->where('type', Web3TransactionsTypeEnum::AirdropStaking->name)
            ->where('status', Web3TransactionsStatusEnum::SUCCESS->name)
            ->order()
            ->paginate($this->perPage());
    }
}
