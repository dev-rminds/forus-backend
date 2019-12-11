<?php

namespace App\Models;

use App\Services\MediaService\Models\Media;
use App\Services\MediaService\Traits\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property int $organization_id
 * @property int $product_category_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float|null $old_price
 * @property int $total_amount
 * @property bool $unlimited_stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $expire_at
 * @property bool $sold_out
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Fund[] $funds
 * @property-read int|null $funds_count
 * @property-read string|null $created_at_locale
 * @property-read bool $expired
 * @property-read bool $is_offer
 * @property-read int $stock_amount
 * @property-read string|null $updated_at_locale
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Services\MediaService\Models\Media[] $medias
 * @property-read int|null $medias_count
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Services\MediaService\Models\Media $photo
 * @property-read \App\Models\ProductCategory $product_category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherTransaction[] $voucher_transactions
 * @property-read int|null $voucher_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Voucher[] $vouchers
 * @property-read int|null $vouchers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Voucher[] $vouchers_reserved
 * @property-read int|null $vouchers_reserved_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereOldPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereProductCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSoldOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereUnlimitedStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasMedia, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'organization_id', 'product_category_id',
        'price', 'old_price', 'total_amount', 'expire_at', 'sold_out',
        'unlimited_stock'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $dates = [
        'expire_at', 'deleted_at'
    ];

    protected $casts = [
        'unlimited_stock' => 'boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vouchers() {
        return $this->hasMany(Voucher::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vouchers_reserved() {
        return $this->hasMany(Voucher::class)->whereDoesntHave('transactions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voucher_transactions() {
        return $this->hasMany(VoucherTransaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product_category() {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function funds() {
        return $this->hasManyThrough(
            Fund::class,
            FundProduct::class
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fund_providers() {
        return $this->belongsToMany(
            FundProvider::class,
            'fund_provider_products'
        );
    }

    /**
     * Get fund logo
     * @return MorphOne
     */
    public function photo() {
        return $this->morphOne(Media::class, 'mediable')->where([
            'type' => 'product_photo'
        ]);
    }

    /**
     * The product is offer
     *
     * @return bool
     */
    public function getIsOfferAttribute() {
        return !!$this->old_price;
    }

    /**
     * The product is sold out
     *
     * @param $value
     * @return bool
     */
    public function getSoldOutAttribute($value) {
        return !!$value;
    }

    /**
     * The product is expired
     *
     * @return bool
     */
    public function getExpiredAttribute() {
        return $this->expire_at->isPast();
    }

    /**
     * Count vouchers generated for this product but not used
     *
     * @return int
     */
    public function countReserved() {
        return $this->vouchers()->doesntHave('transactions')->count();
    }

    /**
     * Count actually sold products
     *
     * @return int
     */
    public function countSold() {
        return $this->voucher_transactions()->count();
    }

    /**
     * @return int
     */
    public function getStockAmountAttribute() {
        return $this->total_amount - (
            $this->vouchers_reserved->count() +
            $this->voucher_transactions->count());
    }

    /**
     * Update sold out state for the product
     */
    public function updateSoldOutState() {
        if (!$this->unlimited_stock) {
            $totalProducts = $this->countReserved() + $this->countSold();

            $this->update([
                'sold_out' => $totalProducts >= $this->total_amount
            ]);
        }
    }

    /**
     * @return Builder
     */
    public static function searchQuery() {

        return Product::query()->where(function(Builder $builder) {
            $activeFunds = Implementation::activeFunds()->pluck('id');
            $builder->whereHas('organization.organization_funds', function(
                Builder $builder
            ) use ($activeFunds) {
                $builder->whereIn('fund_id', $activeFunds->toArray());
                $builder->where('allow_products', true);
            })->orWhereHas('fund_providers');
        })->where('sold_out', false)->where(
            'expire_at', '>', date('Y-m-d')
        );
    }

    /**
     * @param Request $request
     * @return Builder
     */
    public static function search(Request $request) {
        $query = self::searchQuery()->orderBy('created_at', 'desc');

        if ($request->has('product_category_id')) {
            $productCategories = ProductCategory::descendantsAndSelf(
                $request->input('product_category_id')
            )->pluck('id');

            $query->whereIn('product_category_id', $productCategories);
        }

        if ($fund_id = $request->input('fund_id', null)) {
            if ($fund = Fund::find($fund_id)) {
                $providers = $fund->provider_organizations_approved();
                $query->whereIn(
                    'organization_id',
                    $providers->pluck('organizations.id')->toArray()
                );
            }
        }

        if ($request->has('unlimited_stock')) {
            $query->where([
                'unlimited_stock' => !!$request->input('unlimited_stock')
            ]);
        }

        if (!$request->has('q')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($request) {
            return $query
                ->where('name', 'LIKE', "%{$request->input('q')}%")
                ->orWhere('description', 'LIKE', "%{$request->input('q')}%");
        });
    }

    /**
     * @param Request $request
     * @return Builder
     */
    public static function searchAny(Request $request) {
        $query = self::query()->orderBy('created_at', 'desc');

        if ($request->has('unlimited_stock')) {
            $query->where([
                'unlimited_stock' => !!$request->input('unlimited_stock')
            ]);
        }

        if (!$request->has('q')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($request) {
            return $query
                ->where('name', 'LIKE', "%{$request->input('q')}%")
                ->orWhere('description', 'LIKE', "%{$request->input('q')}%");
        });
    }

    public function getFundsWhereIsAvailable()
    {
        $product = $this;

        return Fund::where(
            'state', '!=', Fund::STATE_CLOSED
        )->whereHas('providers', function(
            Builder $builder
        ) use ($product) {
            $builder->where(function(Builder $builder) use ($product) {
                $builder->where('organization_id', $product->organization_id);
                $builder->where('allow_products', true);
            });
            $builder->orWhere(function(Builder $builder) use ($product) {
                $builder->where('organization_id', $product->organization_id);
                $builder->where('allow_products', false);
                $builder->whereHas('fund_provider_products', function(Builder $builder) use ($product) {
                    $builder->where([
                        'product_id' => $product->id,
                    ]);
                });
            });
        });
    }
}
