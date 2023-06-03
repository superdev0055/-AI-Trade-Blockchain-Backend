<?php


namespace App\Modules\Customer;


use App\Helpers\TelegramBot\TelegramBotApi;
use App\Models\CaseDetails;
use App\Models\Cases;
use App\Modules\CustomerBaseController;
use Exception;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

/**
 * @intro cases
 * Class CasesController
 * @package App\Modules\Customer
 */
class CasesController extends CustomerBaseController
{
    /**
     * @intro 列表
     * @return mixed
     * @throws Err
     */
    public function list(): mixed
    {
        $user = $this->getUser();
        return Cases::who($user)
            ->descID()
            ->paginate($this->perPage());
    }

    /**
     * @intro 添加
     * @param Request $request
     * @return array
     * @throws Err
     */
    public function store(Request $request): array
    {
        $params = $request->validate([
            'subject' => 'required|string', #
            'content' => 'required|string', #
        ]);
        $user = $this->getUser();
        $params['users_id'] = $user->id;
        $params['case_id'] = strtoupper(uniqid() . rand(10000, 99999));
        $case = Cases::create($params);
        TelegramBotApi::SendText("请处理新工单\n用户：$user->nickname\n编号：$case->id\n标题：$case->subject");
        return [];
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function show(Request $request): mixed
    {
        $params = $request->validate([
            'id' => 'required|integer', #
        ]);
        $user = $this->getUser();
        $case = Cases::who($user)->with('case_details')->findOrFail($params['id']);
        if ($case->frontend_is_new) {
            $case->frontend_is_new = 0;
            $case->save();
        }
        return $case;
    }

    /**
     * @intro 回复
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function submit(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # case的id
            'answer' => 'required|string', # 回复
        ]);
        $user = $this->getUser();
        $case = Cases::who($user)->findOrFail($params['id']);
        CommonHelper::Trans(function () use ($params, $user, $case) {
            CaseDetails::create([
                'cases_id' => $case->id, #
                'users_id' => $user->id, #
                'answer' => $params['answer'], #
            ]);
            $case->frontend_is_new = 0;
            $case->backend_is_new = 1;
            $case->save();
        });
        TelegramBotApi::SendText("请处理工单回复\n用户：$user->nickname\n编号：$case->id\n标题：$case->subject");
    }
}
