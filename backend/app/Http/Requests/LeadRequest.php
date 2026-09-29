<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+() -]+$/'],
            'source' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(['new', 'in_progress', 'qualified'])],
            'score' => ['required', 'integer', 'between:0,100'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'converted_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'lead_name.required' => 'リード名を入力してください。',
            'email.email' => 'メールアドレスの形式を確認してください。',
            'phone_number.regex' => '電話番号の形式を確認してください。',
            'status.required' => 'ステータスを選択してください。',
            'status.in' => 'ステータスの値を確認してください。',
            'score.required' => 'スコアを入力してください。',
            'score.integer' => 'スコアは整数で入力してください。',
            'score.between' => 'スコアは0から100の範囲で入力してください。',
            'owner_user_id.exists' => '選択した担当者が見つかりません。',
        ];
    }
}
