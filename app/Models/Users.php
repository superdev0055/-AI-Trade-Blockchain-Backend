<?php

namespace App\Models;

use App\Enums\UsersStatusEnum;
use App\Models\Base\BaseUsers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static withParents()
 * @method static each(\Closure $param)
 * @method static whereParentUser(Users $user)
 * @method static find(int $id)
 * @method static where(...$args)
 * @method static today(...$args)
 * @property mixed $vip
 */
class Users extends BaseUsers
{
    use HasApiTokens;

    protected $hidden = ['password'];
    protected string $guard_name = 'sanctum';

    # relations
    public function parent_1(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'parent_1_id', 'id');
    }

    public function parent_2(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'parent_2_id', 'id');
    }

    public function parent_3(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'parent_3_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function withdraw(): HasMany
    {
        return $this->hasMany(Assets::class, 'users_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function staking(): HasMany
    {
        return $this->hasMany(Assets::class, 'users_id', 'id');
    }

    # scopes

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithParents(Builder $query): Builder
    {
        return $query->with('parent_1:id,avatar,address')
            ->with('parent_2:id,avatar,address')
            ->with('parent_3:id,avatar,address');
    }

    /**
     * @param Builder $query
     * @param Users $user
     * @return mixed
     */
    public function scopeWhereParentUser(Builder $query, Users $user): mixed
    {
        return $query->where(function ($q) use ($user) {
            $q->where('parent_1_id', $user->id)
                ->orWhere('parent_2_id', $user->id)
                ->orWhere('parent_3_id', $user->id);
        })->whereStatus(UsersStatusEnum::Enable->name);
    }
}
