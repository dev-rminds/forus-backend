<?php

namespace App\Models;

use App\Services\MediaService\Traits\HasMedia;
use App\Services\MediaService\Models\Media;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\Builder;

/**
 * Class Organization
 * @property mixed $id
 * @property string $identity_address
 * @property string $name
 * @property string $iban
 * @property string $email
 * @property bool $email_public
 * @property string $phone
 * @property bool $phone_public
 * @property string $kvk
 * @property string $btw
 * @property string $website
 * @property int $business_type_id
 * @property bool $website_public
 * @property Media $logo
 * @property BusinessType $business_type
 * @property Collection $funds
 * @property Collection $vouchers
 * @property Collection $products
 * @property Collection $validators
 * @property Collection $supplied_funds
 * @property Collection $supplied_funds_approved
 * @property Collection $organization_funds
 * @property Collection $voucher_transactions
 * @property Collection $funds_voucher_transactions
 * @property Collection $offices
 * @property Collection $employees
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @package App\Models
 */
class Organization extends Model
{
    use HasMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identity_address', 'name', 'iban', 'email', 'email_public',
        'phone', 'phone_public', 'kvk', 'btw', 'website', 'website_public',
        'business_type_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function funds() {
        return $this->hasMany(Fund::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() {
        return $this->hasMany(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function offices() {
        return $this->hasMany(Office::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business_type() {
        return $this->belongsTo(BusinessType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voucher_transactions() {
        return $this->hasMany(VoucherTransaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function funds_voucher_transactions() {
        return $this->hasManyThrough(VoucherTransaction::class, Voucher::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function supplied_funds() {
        return $this->belongsToMany(
            Fund::class,
            'fund_providers'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function supplied_funds_approved() {
        return $this->belongsToMany(
            Fund::class,
            'fund_providers'
        )->where('fund_providers.state', 'approved');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function organization_funds() {
        return $this->hasMany(FundProvider::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function validators() {
        return $this->hasMany(Validator::class);
    }

    /**
     * Get organization logo
     * @return MorphOne
     */
    public function logo() {
        return $this->morphOne(Media::class, 'mediable')->where([
            'type' => 'organization_logo'
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function vouchers() {
        return $this->hasManyThrough(Voucher::class, Fund::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees() {
        return $this->hasMany(Employee::class);
    }

    /**
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder[]|Collection|\Illuminate\Database\Eloquent\Relations\HasMany[]
     */
    public function employeesOfRole(string $role) {
        return $this->employees()->whereHas('roles', function(
            \Illuminate\Database\Eloquent\Builder $query
        ) use ($role) {
            $query->where('key', 'finance');
        })->get();
    }

    /**
     * @return string
     */
    public function emailServiceId() {
        return "organization_" . $this->id;
    }

    /**
     * Returns identity organization roles
     *
     * @param $identityAddress
     * @return Collection
     */
    public function identityRoles($identityAddress) {
        /** @var Employee $employee */
        $employee = $this->employees()->where('identity_address', $identityAddress)->first();

        return $employee ? $employee->roles : collect([]);
    }

    /**
     * Returns identity organization permissions
     * @param $identityAddress
     * @return \Illuminate\Support\Collection
     */
    public function identityPermissions($identityAddress) {
        if (strcmp($identityAddress, $this->identity_address) === 0) {
            return Permission::allMemCached();
        }

        $roles = $this->identityRoles($identityAddress);

        return $roles->pluck('permissions')->flatten()->unique('id');
    }

    /**
     * @param $identityAddress string
     * @param $permissions
     * @param $all boolean
     * @return bool
     */
    public function identityCan(
        string $identityAddress = null,
        $permissions = [],
        $all = true
    ) {
        if (!$identityAddress) {
            return false;
        }

        // as owner of the organization you don't have any restrictions
        if (strcmp($identityAddress, $this->identity_address) === 0) {
            return true;
        }

        // retrieving the list of all the permissions that identity have
        $identityPermissionKeys = $this->identityPermissions(
            $identityAddress
        )->pluck('key');

        // convert string to array
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        if (!$all) {
            return $identityPermissionKeys->intersect($permissions)->count() > 0;
        }

        // check if all the requirements are satisfied
        return $identityPermissionKeys->intersect($permissions)->count() ==
            count($permissions);
    }

    /**
     * @param $identityAddress string
     * @param $permissions
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryByIdentityPermissions (
        string $identityAddress,
        $permissions = false
    ) {
        // convert string to array
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        /**
         * Query all the organizations where identity_address has permissions
         * or is the creator
         */
        return Organization::query()->whereIn('id', function(Builder $query) use ($identityAddress, $permissions) {
            $query->select(['organization_id'])->from((new Employee)->getTable())->where([
                'identity_address' => $identityAddress
            ])->whereIn('id', function (Builder $query) use ($permissions) {
                $query->select('employee_id')->from((new EmployeeRole)->getTable())->whereIn('role_id', function (Builder $query) use ($permissions) {
                    $query->select(['id'])->from((new Role)->getTable())->whereIn('id', function (
                        Builder $query
                    )  use ($permissions) {
                        return $query->select(['role_id'])->from((new RolePermission)->getTable())->whereIn('permission_id', function (Builder $query) use ($permissions) {
                            $query->select('id')->from((new Permission)->getTable());

                            // allow any permission
                            if ($permissions !== false) {
                                $query->whereIn('key', $permissions);
                            }

                            return $query;
                        });
                    })->get();
                });
            });
        })->orWhere('identity_address', $identityAddress);
    }
}
