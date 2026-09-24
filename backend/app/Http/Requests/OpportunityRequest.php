<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(
                    fn ($query) => $query->where('is_deleted', false)
                ),
            ],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'stage_id' => ['required', 'integer', 'exists:opportunity_stages,id'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'opportunity_name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'expected_close_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => '取引先を選択してください。',
            'account_id.exists' => '選択した取引先が見つかりません。',
            'stage_id.required' => '商談ステージを選択してください。',
            'stage_id.exists' => '選択した商談ステージが見つかりません。',
            'owner_user_id.exists' => '選択した担当者が見つかりません。',
            'opportunity_name.required' => '商談名を入力してください。',
            'amount.required' => '商談金額を入力してください。',
            'amount.numeric' => '商談金額は数値で入力してください。',
            'amount.min' => '商談金額は0以上で入力してください。',
            'expected_close_date.date' => '完了予定日の形式を確認してください。',
        ];
    }
}
