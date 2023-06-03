<?php


namespace App\Modules\Admin;


use App\Models\CaseDetails;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;

/**
 * @intro 支持回复
 * Class CaseDetailsController
 * @package App\Modules\Admin
 */
class CaseDetailsController extends AdminBaseController
{
    /**
     * @intro 修改case_details
     * @param Request $request
     * @return array
     */
    public function update(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
			'answer' => 'required|string', #
        ]);
        CaseDetails::idp($params)->update($params);
        return [];
    }

    /**
     * @intro 删除case_details
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        CaseDetails::idp($params)->delete();
        return [];
    }
}
