<?php

namespace App\Http\Requests\Api\Platform\Organizations;

use App\Models\Organization;
use App\Rules\Base\BtwRule;
use App\Rules\Base\IbanRule;
use App\Rules\Base\KvkRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
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
        $kvk = $this->input('kvk');
        $kvkDebug = env("KVK_API_DEBUG", false);
        $kvkGeneric = $kvk == Organization::GENERIC_KVK;

        return [
            'name'                  => 'required|between:2,200',
            'iban'                  => ['required', new IbanRule()],
            'email'                 => 'required|email:strict,dns',
            'email_public'          => 'boolean',
            'phone'                 => 'required|digits_between:6,20',
            'phone_public'          => 'boolean',
            'kvk'                   => [
                'required',
                'digits:8',
                $kvkDebug || $kvkGeneric ? null : 'unique:organizations,kvk',
                $kvkGeneric ? null : new KvkRule(),
            ],
            'btw'                   => ['nullable', 'string', new BtwRule()],
            'website'               => 'nullable|max:200|url',
            'website_public'        => 'boolean',
            'business_type_id'      => 'required|exists:business_types,id',
        ];
    }
}
