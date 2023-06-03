<?php


namespace App\Modules\Customer;


use App\Models\SysMessages;
use App\Modules\CustomerBaseController;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class SysMessagesController
 * @package App\Modules\Customer
 */
class SysMessagesController extends CustomerBaseController
{
    /**
     * @intro 是否有新消息
     * @return array
     * @throws Err
     */
    #[ArrayShape(['has_new_message' => "mixed"])]
    public function hasNewMessage(): array
    {
        $user = $this->getUser();
        $exists = SysMessages::where('users_id', $user->id)
            ->where('has_read', false)
            ->exists();

        return [
            'has_new_message' => $exists
        ];
    }

    /**
     * @intro 列表
     * @return mixed
     * @throws Err
     */
    public function list(): mixed
    {
        $user = $this->getUser();

        return SysMessages::where('users_id', $user->id)
            ->orderByDesc('id')
            ->paginate($this->perPage());
    }

    /**
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function show(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $user = $this->getUser();
        $msg = SysMessages::where('users_id', $user->id)->find($params['id']);
        if (!$msg)
            Err::Throw(__("No message found"));

        if (!$msg->has_read) {
            $msg->has_read = true;
            $msg->save();
        }
    }
}
