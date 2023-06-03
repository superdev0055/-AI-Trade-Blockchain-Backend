<?php

namespace App\Exports;

use App\Enums\UsersIdentityStatusEnum;
use App\Enums\UsersProfileStatusEnum;
use App\Enums\UsersStatusEnum;
use App\Models\Users;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    private array $params;
    private int $page;
    private int $perPage;

    public function __construct(array $params, int $page, int $perPage)
    {
        $this->params = $params;
        $this->page = $page;
        $this->perPage = $perPage;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $params = $this->params;
        $query = Users::withParents()
            ->with('vip:id,name');

        if ($params['download_type'] == 3)
            $list = $query->order()->get()->toArray();
        else
            $list = $query->ifWhereLike($params, 'nickname')
                ->ifWhereLike($params, 'full_name')
                ->ifWhereLike($params, 'address')
                ->ifWhereHas($params, 'parent_address', 'parent_1', function ($q) use ($params) {
                    $q->where('address', 'like', "%{$params['parent_address']}%");
                })
                ->when(isset($params['email_verified']), function ($q) use ($params) {
                    return $params['email_verified'] == 2 ? $q->where('email_verified_at', '!=', null) : $q->where('email_verified_at', null);
                })
                ->ifWhere($params, 'profile_status')
                ->ifWhere($params, 'identity_status')
                ->ifWhere($params, 'status')
                ->when(isset($params['trailed']), function ($q) use ($params) {
                    return $params['trailed'] == 2 ? $q->where('trailed_at', '!=', null) : $q->where('trailed_at', null);
                })
                ->order()
                ->download($params, $this->page, $this->perPage)
                ->get()
                ->toArray();

        $data = [];
        foreach ($list as $item) {
            $arr = [
                $item['id'],
                $item['nickname'],
                $item['platform'],
                $item['address'],
                $item['parent_1_address']['address'] ?? '-',
                $item['parent_2_address']['address'] ?? '-',
                $item['parent_3_address']['address'] ?? '-',
                $item['total_balance'],
                $item['total_staking_amount'],
                $item['total_income'],
                $item['total_loyalty_value'],
                $item['trailed_at'],
                $item['email_verified_at'],
                $item['profile_verified_at'],
                $item['identity_verified_at'],
                UsersProfileStatusEnum::from($item['profile_status'])->name,
                UsersIdentityStatusEnum::from($item['identity_status'])->name,
                UsersStatusEnum::from($item['status'])->name,
                $item['created_at'],
            ];
            $data[] = $arr;
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'id',
            'nickname',
            'platform',
            'address',
            'parent 1 address',
            'parent 2 address',
            'parent 3 address',
            'total balance',
            'total stakings',
            'total income',
            'total loyalty',
            'trailed_at',
            'email_verified_at',
            'profile_verified_at',
            'identity_verified_at',
            'profile_status',
            'identity_status',
            'status',
            'created_at',
        ];
    }
}
