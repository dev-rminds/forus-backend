<?php

namespace App\Http\Requests\Api\Platform\Organizations\Products;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var Product $product */
        $product = request()->product;
        $currentExpire = $product->expire_at->format('Y-m-d');
        $minAmount = $product->countReserved() + $product->countSold();

        $price = rule_number_format($this->get('price', 0));

        return [
            'name'                  => 'required|between:2,200',
            'description'           => 'required|between:5,1000',
            'price'                 => 'required|numeric|min:.2',
            'old_price'             => [
                'nullable',
                'numeric',
                'min:' . $price
            ],
            'total_amount'          => [
                $product->unlimited_stock ? null : 'required',
                'numeric',
                $product->unlimited_stock ? null : 'min:' . $minAmount,
            ],
            'expire_at'             => 'required|date|after:today|after_or_equal:' . $currentExpire,
            'product_category_id'   => 'required|exists:product_categories,id',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'expire_at.after' => trans('validation.after', [
                'date' => trans('validation.attributes.today')
            ])
        ];
    }
}
    