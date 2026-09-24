<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SupportCaseRequest extends FormRequest
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
            'status_id' => ['required', 'integer', 'exists:case_statuses,id'],
            'owner_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'priority' => ['required', 'string', Rule::in(['high', 'middle', 'low'])],
            'opened_at' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $contactId = $this->integer('contact_id');
            $accountId = $this->integer('account_id');

            if ($contactId === 0 || $accountId === 0 || $validator->errors()->hasAny(['account_id', 'contact_id'])) {
                return;
            }

            if (! DB::table('contacts')->where('id', $contactId)->where('account_id', $accountId)->exists()) {
                $validator->errors()->add('contact_id', '選択した連絡先と取引先が一致しません。');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => '取引先を選択してください。',
            'account_id.exists' => '選択した取引先が見つかりません。',
            'contact_id.exists' => '選択した連絡先が見つかりません。',
            'status_id.required' => '問い合わせステータスを選択してください。',
            'status_id.exists' => '選択した問い合わせステータスが見つかりません。',
            'owner_user_id.exists' => '選択した担当者が見つかりません。',
            'subject.required' => '件名を入力してください。',
            'priority.required' => '優先度を選択してください。',
            'priority.in' => '優先度の値を確認してください。',
            'opened_at.required' => '受付日時を入力してください。',
            'opened_at.date' => '受付日時の形式を確認してください。',
        ];
    }
}
