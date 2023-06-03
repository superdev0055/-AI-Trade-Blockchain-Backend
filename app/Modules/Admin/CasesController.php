<?php


namespace App\Modules\Admin;


use App\Models\CaseDetails;
use App\Models\Cases;
use App\Modules\AdminBaseController;
use App\NewLogics\SysMessageLogics;
use App\NewServices\UsersServices;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

/**
 * @intro cases
 * Class CasesController
 * @package App\Modules\Admin
 */
class CasesController extends AdminBaseController
{
    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function list(Request $request): mixed
    {
        $params = $request->validate([
            'users_address' => 'nullable|string', # 用户地址
            'backend_is_new' => 'nullable|boolean', # Admin Has Read
            'status' => 'nullable|integer', # 1:Pending / 2:Closed
        ]);
        $this->YesOrNo($params, 'backend_is_new');
        return Cases::ifWhereHasUserAddress($params)
            ->ifWhere($params, 'status')
            ->ifWhere($params, 'backend_is_new')
            ->withUser()
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function show(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', #
        ]);
        return Cases::withUser()
            ->with('case_details')
            ->findOrFail($params['id'])
            ->toArray();
    }

    /**
     * @intro 回复
     * @param Request $request
     * @throws Exception
     */
    public function submit(Request $request)
    {
        $params = $request->validate([
            'id' => 'required|integer', # case的id
            'answer' => 'required|string', # 回复
        ]);
        CommonHelper::Trans(function () use ($params) {
            $case = Cases::findOrFail($params['id']);
            $detail = CaseDetails::create([
                'cases_id' => $case->id, #
//                'users_id' => $user->id, #
                'answer' => $params['answer'], #
            ]);
            $case->frontend_is_new = 1;
            $case->backend_is_new = 0;
            $case->save();

            $user = UsersServices::GetById($case->users_id);
            SysMessageLogics::SupportAnswered($user, $detail);
        });
    }

    /**
     * @intro 关闭
     * @param Request $request
     * @return void
     */
    public function close(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', #
            'status' => 'required|string', # Closed,Pending
        ]);
        Cases::findOrFail($params['id'])->update([
            'status' => $params['status']
        ]);
    }

    /**
     * @intro 更新case
     * @param Request $request
     * @return array
     */
    public function update(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', #
            'content' => 'required|string', # 内容
            'status' => 'required|string', # status:Pending,Closed
        ]);
        Cases::idp($params)->update($params);
        return [];
    }

    /**
     * @intro 删除case和case_details
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', #
        ]);
        DB::transaction(function () use ($params) {
            Cases::idp($params)->delete();
            CaseDetails::where('cases_id', $params['id'])->delete();
        });
        return [];
    }
}
