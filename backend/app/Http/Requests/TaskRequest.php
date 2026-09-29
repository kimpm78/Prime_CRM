<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'nullable',
                'required_with:opportunity_id',
                'integer',
                Rule::exists('accounts', 'id')->where(
                    fn ($query) => $query->where('is_deleted', false)
                ),
            ],
            'opportunity_id' => [
                'nullable',
                'integer',
                Rule::exists('opportunities', 'id')->where(
                    fn ($query) => $query->where('is_deleted', false)
                ),
            ],
            'assigned_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(['not_started', 'in_progress', 'completed'])],
            'priority' => ['required', 'string', Rule::in(['high', 'middle', 'low'])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $opportunityId = $this->integer('opportunity_id');
            $accountId = $this->integer('account_id');

            if ($opportunityId === 0 || $accountId === 0 || $validator->errors()->has('opportunity_id')) {
                return;
            }

            $belongsToAccount = DB::table('opportunities')
                ->where('id', $opportunityId)
                ->where('account_id', $accountId)
                ->where('is_deleted', false)
                ->exists();

            if (! $belongsToAccount) {
                $validator->errors()->add('opportunity_id', '選択した商談と取引先が一致しません。');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'account_id.required_with' => '商談を選択する場合は取引先も選択してください。',
            'account_id.exists' => '選択した取引先が見つかりません。',
            'opportunity_id.exists' => '選択した商談が見つかりません。',
            'assigned_user_id.required' => '担当者を選択してください。',
            'assigned_user_id.exists' => '選択した担当者が見つかりません。',
            'title.required' => 'タスク名を入力してください。',
            'due_date.date' => '期限の形式を確認してください。',
            'status.required' => '状態を選択してください。',
            'status.in' => '状態の値を確認してください。',
            'priority.required' => '優先度を選択してください。',
            'priority.in' => '優先度の値を確認してください。',
        ];
    }
}
