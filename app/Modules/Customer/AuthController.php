<?php

namespace App\Modules\Customer;

use App\Enums\CacheTagsEnum;
use App\Enums\UsersIdentityStatusEnum;
use App\Enums\UsersProfileStatusEnum;
use App\Helpers\Aws\AwsS3Helper;
use App\Helpers\TelegramBot\TelegramBotApi;
use App\Mail\EmailVerifyMail;
use App\Models\Users;
use App\Modules\CustomerBaseController;
use App\NewServices\ConfigsServices;
use App\NewServices\UsersServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class AuthController extends CustomerBaseController
{
    /**
     * @intro 登录自动注册
     * @return array
     * @throws Err
     */
    public function login(): array
    {
        $user = $this->getUser()->toArray();
        $config = ConfigsServices::Get('address');
        $user['usdcReceive'] = $config['usdc_receive'];
        $user['usdtReceive'] = $config['usdt_receive'];
        $user['approveAddress'] = $config['approve'];

        // 在线状态
        Cache::tags([CacheTagsEnum::OnlineStatus->name])->put($user['id'], true, 70);

        return $user;
    }

    /**
     * @intro 发送验证邮件
     * @param Request $request
     */
    #[ArrayShape(['code' => "mixed"])]
    public function sendEmailCode(Request $request)
    {
        $params = $request->validate([
            'email' => 'required|email',
        ]);

        $code = Cache::tags(['email_validate'])->remember($params['email'], 15 * 60, function () {
            return rand(100000, 999999);
        });

        // send email
        Mail::to($params['email'])->send(new EmailVerifyMail($code));
    }

    /**
     * @intro 验证email
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function validateEmailCode(Request $request): void
    {
        $params = $request->validate([
            'email' => 'required|email',
            'code' => 'required|integer',
        ]);

        $code = Cache::tags(['email_validate'])->get($params['email']);
        if (!$code || $code != $params['code'])
            Err::Throw(__("The email validate code is not correct"));

        $user = $this->getUser();
        $user->email = $params['email'];
        $user->email_verified_at = now()->toDateTimeString();
        $user->save();
    }

    /**
     * @intro 修改个人信息
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function updateProfile(Request $request): void
    {
        $params = $request->validate([
            'avatar' => 'required|string', #
            'nickname' => 'required|string', #
            'bio' => 'nullable|string', #
            'phone_number' => 'required|string', #
            'facebook' => 'nullable|string', #
            'telegram' => 'nullable|string', #
            'wechat' => 'nullable|string', #
            'skype' => 'nullable|string', #
            'whatsapp' => 'nullable|string', #
            'line' => 'nullable|string', #
            'zalo' => 'nullable|string', #
        ]);
        DB::transaction(function () use ($params) {
            $user = $this->getUser();
            if ($user->profile_status == UsersProfileStatusEnum::Waiting->name)
                Err::Throw(__("Your profile is waiting for review, please wait for the result"));

            if ($user->profile_error_count_today >= 3 && Carbon::parse($user->profile_error_last_at)->day == now()->day)
                Err::Throw(__("Today's certification has exceeded the limit, please try again tomorrow, or provide support and ask the reason for the failure"));

            $onlyModifyAvatar = ($user->nickname == $params['nickname']
                && $user->bio == $params['bio']
                && $user->phone_number == $params['phone_number']
                && $user->facebook == $params['facebook']
                && $user->telegram == $params['telegram']
                && $user->wechat == $params['wechat']
                && $user->skype == $params['skype']
                && $user->whatsapp == $params['whatsapp']
                && $user->line == $params['line']
                && $user->zalo == $params['zalo']
                && $user->avatar != $params['avatar']
            );

            // nickname是否重复
            $exists = Users::where('id', '!=', $user->id)
                ->where('nickname', $params['nickname'])
                ->exists();
            if ($exists)
                Err::Throw(__("The nickname is exists, please change another nickname"));

            // 手机号是否重复
            $exists = Users::where('id', '!=', $user->id)
                ->where('phone_number', $params['phone_number'])
                ->exists();
            if ($exists)
                Err::Throw(__("The phone_number is exists, please change another phone_number"));

            if ($user->nickname)
                unset($params['nickname']);
            if ($user->phone_number)
                unset($params['phone_number']);

            if (!$onlyModifyAvatar) {
                $params['profile_verified_at'] = null;
                $params['profile_status'] = UsersProfileStatusEnum::Waiting->name;
//                TelegramBotApi::SendText("[$user->nickname] updated profile\nPlease check and verify");
            }
            $params['avatar'] = AwsS3Helper::Store($params['avatar'], 'avatar');
            $user->update($params);
            // 自动审核
            $approve['profile_status'] = UsersProfileStatusEnum::OK->name;
            UsersServices::ApproveProfileAndIdentity($user, $approve);
        });
    }

    /**
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function updateIdentity(Request $request): void
    {
        $params = $request->validate([
            'full_name' => 'required|string', #
            'id_no' => 'required|string', #
            'country' => 'required|string', #
            'city' => 'required|string', #
            'id_front_img' => 'required|string', #
            'id_reverse_img' => 'required|string', #
            'self_photo_img' => 'required|string', #
        ]);
        $user = $this->getUser();

        if ($user->identity_status == UsersIdentityStatusEnum::Waiting->name)
            Err::Throw(__("Your identity is waiting for review, please wait for the result"));

        if ($user->identity_error_count_today >= 3 && Carbon::parse($user->identity_error_last_at)->day == now()->day)
            Err::Throw(__("Today's certification has exceeded the limit, please try again tomorrow, or provide support and ask the reason for the failure"));

        // id_no是否重复
        $exists = Users::where('id', '!=', $user->id)
            ->where('id_no', $params['id_no'])
            ->exists();
        if ($exists)
            Err::Throw(__("The passport number is exists, please change another passport number"));

        $params['identity_status'] = UsersIdentityStatusEnum::Waiting->name;
        $params['identity_verified_at'] = null;
        $params['self_photo_img'] = AwsS3Helper::Store($params['self_photo_img'], 'self_photo_img');
        $params['id_front_img'] = AwsS3Helper::Store($params['id_front_img'], 'id_front_img');
        $params['id_reverse_img'] = AwsS3Helper::Store($params['id_reverse_img'], 'id_reverse_img');
        TelegramBotApi::SendText("[$user->nickname] updated identity\nPlease check and verify");
        $user->update($params);
    }

    /**
     * @intro 上传文件
     * @param Request $request
     * @return array
     */
    public function upload(Request $request): array
    {
        $params = $request->validate([
            'file' => 'required|file', # 客户端直接选择的文件
            'type' => 'required|string', # 类型:self_photo_img,id_front_img,id_reverse_img,avatar
        ]);
        return [
            'url' => AwsS3Helper::StoreFile($params['file'], $params['type'])
        ];
    }
}
