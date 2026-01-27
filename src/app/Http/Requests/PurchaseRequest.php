<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;

class PurchaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->address_id === '') {
            $this->merge(['address_id' => null]);
        }

        if ($this->payment_method === '') {
            $this->merge(['payment_method' => null]);
        }
    }

    public function rules()
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'payment_method' => ['required', 'in:konbini,card'],
            'address_id' => ['nullable', 'exists:addresses,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $itemId = $this->item_id;
            $user = Auth::user();

            $hasSessionAddress = session()->has("purchase_address_{$itemId}");

            $hasItemAddress = Address::where('user_id', $user->id)
                ->where('item_id', $itemId)
                ->exists();

            $hasUserAddress =
                $user &&
                ! empty(trim($user->postal_code)) &&
                ! empty(trim($user->address));

            if (! $hasSessionAddress && ! $hasItemAddress && ! $hasUserAddress) {
                $validator->errors()->add(
                    'address',
                    '配送先を設定してください。'
                );
            }
        });
    }


    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください。',
            'payment_method.in' => '支払い方法が正しくありません。',
        ];
    }
}
