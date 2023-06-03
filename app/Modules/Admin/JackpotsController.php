<?php


namespace App\Modules\Admin;


use App\Models\Jackpots;
use App\Modules\AdminBaseController;
use LaravelCommon\App\Exceptions\Err;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @intro
 * Class JackpotsController
 * @package App\Modules\Admin
 */
class JackpotsController extends AdminBaseController
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
            'status' => 'required|string', # :OnGoing,Finished
        ]);
        return Jackpots::ifWhere($params, 'status')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @param Request $request
     * @return array
     */
    #[ArrayShape(['model' => "", 'statistics' => "array[]"])]
    public function show(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $model = Jackpots::idp($params);
        return [
            'model' => $model,
            'statistics' => [
                ['title' => 'Goal', 'value' => $model->goal],
                ['title' => 'Balance', 'value' => $model->balance],
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function select(): mixed
    {
        return Jackpots::selectRaw("id, CONTACT(id,_,status)")
            ->order()
            ->get();
    }
}
