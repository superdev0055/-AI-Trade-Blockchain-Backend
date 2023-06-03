<?php

namespace App\NewServices;

use App\Enums\UsersIdentityStatusEnum;
use App\Enums\UsersProfileStatusEnum;
use App\Models\Users;
use App\NewLogics\SysMessageLogics;
use Carbon\Carbon;
use Exception;
use Generator;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vinkla\Hashids\Facades\Hashids;
use Web3\Utils;

class UsersServices
{
    /**
     * @ok
     * @param Users $user
     * @return void
     */
    public static function DoNotShowCard(Users $user): void
    {
        $user->show_card_at = now()->toDateTimeString();
        $user->save();
    }

    /**
     *  function name getFirstWithdrawalFree
     *  get user first_withdrawal_free value
     */
    public static function getFirstWithdrawalStatus(Users $user): bool
    {
        if (!$user->first_withdrawal_free) {
            return false;
        }
        return true;
    }

    /**
     * @param int $id
     * @param bool $throw
     * @return Users
     * @throws Err
     */
    public static function GetById(int $id, bool $throw = false): Users
    {
        $user = Users::find($id);
        if (!$user && $throw)
            Err::Throw(__("User does not exist"));
        return $user;
    }

    /**
     * @ok
     * @param string $address
     * @param bool $throw
     * @return ?Users
     * @throws Err
     */
    public static function GetByAddress(string $address, bool $throw = false): ?Users
    {
        $user = Users::where('address', $address)->first();
        if (!$user && $throw)
            Err::Throw(__("address does not exist"));
        return $user;
    }

    /**
     * @param int $usersId
     * @return Users
     * @throws Err
     */
    public static function GetUserById(int $usersId): Users
    {
        $user = Users::find($usersId);
        if (!$user)
            Err::Throw(__("The user is not exists"));
        return $user;
    }

    /**
     * @ok
     * @param array $account
     * @param bool $isFakeUser
     * @return Users
     * @throws Err
     */
    public static function AutoRegisterUser(array $account, bool $isFakeUser = false): Users
    {
        $address = $account['address'] ?? null;
        $inviteCode = $account['inviteCode'] ?? null;
        $avatar = $account['avatar'] ?? null;
        $email = $account['email'] ?? null;
        $nickname = $account['nickname'] ?? null;

        $isAddress = Utils::isAddress($address);
        if (!$isAddress)
            Err::Throw(__("Your wallet address is wrong"));

        $user = Users::where('address', $address)->first();
        if ($user)
            return $user;

        $parent = $parent1Id = $parent2Id = $parent3Id = null;
        if ($inviteCode) {
            $parent = Users::where('invite_code', $inviteCode)->first();
            if ($parent) {
                $parent1Id = $parent->id;
                $parent2Id = $parent->parent_1_id;
                $parent3Id = $parent->parent_2_id;
            }

            // 添加数字
            if ($parent1Id) Users::find($parent1Id)->increment('referral_count');
            if ($parent2Id) Users::find($parent2Id)->increment('referral_count');
            if ($parent3Id) Users::find($parent3Id)->increment('referral_count');
        }

        $user = Users::create([
            'parent_1_id' => $parent1Id,
            'parent_2_id' => $parent2Id,
            'parent_3_id' => $parent3Id,
            'address' => $address,
            'avatar' => $avatar,
            'email' => $email,
            'nickname' => $nickname,
        ]);

        if ($isFakeUser) {
            Log::debug('is fake user');
            $now = now()->toDateTimeString();
            $params = [
                'can_automatic_trade' => true,
                'show_card_at' => $now,
                'trailed_at' => $now,
                'can_automatic_exchange' => true,
                'can_profit_guarantee' => true,
                'can_leveraged_investment' => true,
                'can_automatic_loan_repayment' => true,
                'can_automatic_airdrop_bonus' => true,
                'is_cool_user' => true,
            ];
            $user->update($params);
        }

        Users::findOrFail($user->id)->update([
            'invite_code' => Hashids::connection('invite_code')->encode($user->id)
        ]);

        // friends
        if ($parent1Id) {
            FriendsServices::BothFollow($user, $parent);
        }

        return $user;
    }

    /**
     * @param mixed $query
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public static function Export(mixed $query): StreamedResponse|string
    {
        /**
         * @param $query
         * @return Generator
         */
        function dateGenerator($query): Generator
        {
            foreach ($query->lazy() as $item) {
                yield $item;
            }
        }

        $uniqid = uniqid();
        return (new FastExcel(dateGenerator($query)))->download("用户表_$uniqid.xlsx", function ($item) {
            return [
                'id' => $item->id,
                '昵称' => $item->nickname ?? '-',
                '状态' => $item->status,
            ];
        });
    }

    /**
     * @param Users $user
     * @param array $params
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function ApproveProfileAndIdentity(Users $user, array $params): void
    {
        // 如果输入了上级地址
        if (isset($params['parent_address'])) {
            $parent = UsersServices::GetByAddress($params['parent_address'], throw: true);
            if ($parent->id == $user->id)
                Err::Throw(__("Can't fill in your own address"));
            if ($parent->id != $user->parent_1_id) {
                $parent1Id = $parent->id;
                $parent2Id = $parent->parent_1_id;
                $parent3Id = $parent->parent_2_id;
                $user->update([
                    'parent_1_id' => $parent1Id,
                    'parent_2_id' => $parent2Id,
                    'parent_3_id' => $parent3Id,
                ]);
                Users::where('parent_1_id', $user->id)->update([
                    'parent_2_id' => $user->parent_1_id,
                    'parent_3_id' => $user->parent_2_id
                ]);
                Users::where('parent_2_id', $user->id)->update([
                    'parent_3_id' => $user->parent_2_id
                ]);
                // 互关
                FriendsServices::BothFollow($user, $parent);
            }
        }

        if (isset($params['profile_status'])) {
            switch ($params['profile_status']) {
                case UsersProfileStatusEnum::OK->name:
                    $params['profile_verified_at'] = now()->toDateTimeString();
                    $params['profile_error_message'] = null;
                    $params['profile_error_last_at'] = null;
                    $params['profile_error_count_today'] = 0;
                    $user->update($params);
                    SysMessageLogics::ProfileVerify($user);
                    break;
                case UsersProfileStatusEnum::Failed->name:
                    if (!isset($params['profile_error_message']))
                        Err::Throw(__("If the audit is not passed, the reason needs to be provided"));
                    // 计算失败次数
                    $toZero = false;
                    if (!$user->profile_error_last_at) {
                        $toZero = true;
                    } else {
                        if (Carbon::parse($user->profile_error_last_at)->day != now()->day)
                            $toZero = true;
                    }
                    $params['profile_verified_at'] = null;
                    $params['profile_error_last_at'] = now()->toDateTimeString();
                    $params['profile_error_count_today'] = $toZero ? 1 : $user->profile_error_count_today + 1;
                    $user->update($params);
                    // 发送站内信
                    SysMessageLogics::ProfileVerifyFailed($user);
                    break;
                default:
                    $user->update($params);
                    break;
            }
        }

        if (isset($params['identity_status'])) {
            switch ($params['identity_status']) {
                case UsersIdentityStatusEnum::OK->name:
                    $params['identity_verified_at'] = now()->toDateTimeString();
                    $params['identity_error_message'] = null;
                    $params['identity_error_last_at'] = null;
                    $params['identity_error_count_today'] = 0;
                    $user->update($params);
                    // send bonus
                    BonusesServices::CreateByVerifyIdentity($user);
                    SysMessageLogics::IdentityVerify($user);
                    break;
                case UsersIdentityStatusEnum::Failed->name:
                    if (!isset($params['identity_error_message']))
                        Err::Throw(__("If the audit is not passed, the reason needs to be provided"));
                    // 计算失败次数
                    $toZero = false;
                    if (!$user->identity_error_last_at) {
                        $toZero = true;
                    } else {
                        if (Carbon::parse($user->identity_error_last_at)->day != now()->day)
                            $toZero = true;
                    }
                    $params['identity_verified_at'] = null;
                    $params['identity_error_last_at'] = now()->toDateTimeString();
                    $params['identity_error_count_today'] = $toZero ? 1 : $user->identity_error_count_today + 1;
                    $user->update($params);
                    // 发送站内信
                    SysMessageLogics::IdentityVerifyFailed($user);
                    break;
                default:
                    $user->update($params);
                    break;
            }
        }
    }
}
