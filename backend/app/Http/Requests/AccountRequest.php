<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $account = $this->route('account');

        return [
            'account_code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('accounts', 'account_code')->ignore($account),
            ],
            'account_name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+() -]+$/'],
            'website' => ['nullable', 'url', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10', 'regex:/^[0-9-]+$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'memo' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_code.required' => '取引先コードを入力してください。',
            'account_code.regex' => '取引先コードは英数字とハイフンで入力してください。',
            'account_code.unique' => 'この取引先コードは既に使用されています。',
            'account_name.required' => '取引先名を入力してください。',
            'phone_number.regex' => '電話番号の形式を確認してください。',
            'postal_code.regex' => '郵便番号の形式を確認してください。',
            'website.url' => 'WebサイトはURL形式で入力してください。',
        ];
    }
}
