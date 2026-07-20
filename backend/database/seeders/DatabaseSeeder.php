<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $adminRole = $this->upsertId('roles', 'role_name', 'admin', [
                'description' => '全機能を利用可能', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $salesRole = $this->upsertId('roles', 'role_name', 'sales', [
                'description' => '顧客・商談・活動を利用可能', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->upsertId('roles', 'role_name', 'viewer', [
                'description' => '参照のみ可能', 'created_at' => $now, 'updated_at' => $now,
            ]);

            $admin = User::updateOrCreate(['email' => 'admin@prime-crm.test'], [
                'role_id' => $adminRole, 'name' => '佐藤 美咲', 'password' => Hash::make('password'),
                'department' => '営業企画', 'is_active' => true,
            ]);
            $sales = User::updateOrCreate(['email' => 'sales@prime-crm.test'], [
                'role_id' => $salesRole, 'name' => '田中 健太', 'password' => Hash::make('password'),
                'department' => '法人営業', 'is_active' => true,
            ]);

            $accounts = [
                ['AC-1001', '株式会社ネクストウェーブ', 'IT・通信', '03-6821-4567', 'https://example.com', '100-0005', '東京都千代田区丸の内1-1-1', $admin->id, '新規DX基盤を検討中'],
                ['AC-1002', '青空商事株式会社', '卸売・小売', '06-6123-8801', null, '530-0001', '大阪府大阪市北区梅田2-4-9', $sales->id, '既存契約の更新候補'],
                ['AC-1003', '北斗メディカル合同会社', '医療・福祉', '011-222-3098', null, '060-0002', '北海道札幌市中央区北二条西3-5', $admin->id, 'セキュリティ要件を確認'],
                ['AC-1004', 'Sunrise Studio', '広告・メディア', '045-310-7272', 'https://example.org', '220-0012', '神奈川県横浜市西区みなとみらい4-2', $sales->id, null],
                ['AC-1005', '京浜ロジスティクス株式会社', '運輸・物流', '03-5442-1910', null, '108-0022', '東京都港区海岸3-8-4', $sales->id, '物流拠点3箇所への展開を計画'],
                ['AC-1006', 'みらい教育研究所', '教育', '052-990-2456', null, '460-0008', '愛知県名古屋市中区栄3-7-12', $admin->id, null],
            ];
            $accountIds = [];
            foreach ($accounts as [$code, $name, $industry, $phone, $website, $postal, $address, $owner, $memo]) {
                $accountIds[$code] = $this->upsertId('accounts', 'account_code', $code, [
                    'account_name' => $name, 'industry' => $industry, 'phone_number' => $phone,
                    'website' => $website, 'postal_code' => $postal, 'address' => $address,
                    'owner_user_id' => $owner, 'memo' => $memo, 'is_deleted' => false,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            foreach ([
                [$accountIds['AC-1001'], '山田', '太郎', '情報システム部', '部長', 'yamada@example.com', true],
                [$accountIds['AC-1002'], '高橋', '彩', '営業推進部', '課長', 'takahashi@example.com', true],
                [$accountIds['AC-1003'], '鈴木', '直樹', '経営企画室', '室長', 'suzuki@example.com', true],
                [$accountIds['AC-1005'], '伊藤', '恵', 'DX推進部', 'マネージャー', 'ito@example.com', true],
            ] as [$account, $last, $first, $department, $position, $email, $primary]) {
                DB::table('contacts')->updateOrInsert(
                    ['account_id' => $account, 'email' => $email],
                    ['last_name' => $last, 'first_name' => $first, 'department' => $department,
                        'position' => $position, 'is_primary' => $primary, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $stageData = [
                ['lead', 10, 1], ['proposal', 30, 2], ['quote', 60, 3], ['won', 100, 4], ['lost', 0, 5],
            ];
            $stageIds = [];
            foreach ($stageData as [$name, $probability, $order]) {
                $stageIds[$name] = $this->upsertId('opportunity_stages', 'stage_name', $name, [
                    'probability' => $probability, 'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $opportunities = [
                ['DX統合基盤 導入プロジェクト', 'AC-1001', 'proposal', 4800000, now()->addDays(28)->toDateString(), $admin->id],
                ['在庫管理クラウド更新', 'AC-1002', 'quote', 2650000, now()->addDays(12)->toDateString(), $sales->id],
                ['患者ポータル構築支援', 'AC-1003', 'lead', 7200000, now()->addDays(55)->toDateString(), $admin->id],
                ['マーケティング分析パック', 'AC-1004', 'won', 1580000, now()->subDays(8)->toDateString(), $sales->id],
                ['配送可視化システム', 'AC-1005', 'quote', 6300000, now()->addDays(21)->toDateString(), $sales->id],
                ['教育データ活用 PoC', 'AC-1006', 'proposal', 1200000, now()->addDays(35)->toDateString(), $admin->id],
            ];
            $opportunityIds = [];
            foreach ($opportunities as [$name, $accountCode, $stage, $amount, $closeDate, $owner]) {
                $opportunityIds[$name] = $this->upsertId('opportunities', 'opportunity_name', $name, [
                    'account_id' => $accountIds[$accountCode], 'stage_id' => $stageIds[$stage],
                    'owner_user_id' => $owner, 'amount' => $amount, 'expected_close_date' => $closeDate,
                    'description' => '要件ヒアリングと提案内容を継続調整中', 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            foreach ([
                ['要件整理資料の送付', 'AC-1001', 'DX統合基盤 導入プロジェクト', $admin->id, 2, 'in_progress', 'high'],
                ['見積条件の最終確認', 'AC-1002', '在庫管理クラウド更新', $sales->id, 1, 'not_started', 'high'],
                ['医療情報ガイドライン確認', 'AC-1003', '患者ポータル構築支援', $admin->id, 5, 'in_progress', 'middle'],
                ['導入スケジュール調整', 'AC-1005', '配送可視化システム', $sales->id, 7, 'not_started', 'middle'],
                ['PoCデータ項目の確認', 'AC-1006', '教育データ活用 PoC', $admin->id, 10, 'not_started', 'low'],
            ] as [$title, $accountCode, $opportunity, $user, $days, $status, $priority]) {
                DB::table('tasks')->updateOrInsert(['title' => $title], [
                    'account_id' => $accountIds[$accountCode], 'opportunity_id' => $opportunityIds[$opportunity],
                    'assigned_user_id' => $user, 'description' => '次回アクションを実施し、結果を活動履歴へ登録する。',
                    'due_date' => now()->addDays($days)->toDateString(), 'status' => $status, 'priority' => $priority,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            foreach ([['new', 1], ['in_progress', 2], ['resolved', 3]] as [$name, $order]) {
                $caseStatusIds[$name] = $this->upsertId('case_statuses', 'status_name', $name, [
                    'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            foreach ([
                ['管理画面にログインできない', 'AC-1002', 'new', 'high'],
                ['CSV出力の文字化けについて', 'AC-1004', 'in_progress', 'middle'],
                ['月次レポートの抽出条件', 'AC-1001', 'resolved', 'low'],
                ['モバイル表示で項目が欠ける', 'AC-1005', 'in_progress', 'middle'],
            ] as [$subject, $accountCode, $status, $priority]) {
                DB::table('cases')->updateOrInsert(['subject' => $subject], [
                    'account_id' => $accountIds[$accountCode], 'status_id' => $caseStatusIds[$status],
                    'owner_user_id' => $admin->id, 'description' => 'お客様からの問い合わせ内容を確認し対応する。',
                    'priority' => $priority, 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            foreach ([
                ['ネクストウェーブ株式会社', '山本 智子', 'Web', 'new', 92],
                ['株式会社オービット', '中村 海斗', '展示会', 'in_progress', 78],
                ['東洋フーズ株式会社', '小林 由佳', '紹介', 'new', 71],
                ['シードデザイン合同会社', '加藤 翼', 'Web', 'in_progress', 64],
                ['光和テクノロジー', '吉田 玲奈', '電話', 'qualified', 58],
            ] as [$lead, $contact, $source, $status, $score]) {
                DB::table('leads')->updateOrInsert(['lead_name' => $lead], [
                    'contact_name' => $contact, 'email' => strtolower(str_replace(' ', '.', $contact)).'@example.com',
                    'source' => $source, 'status' => $status, 'score' => $score,
                    'owner_user_id' => $sales->id, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            foreach ([
                ['PRD-001', 'CRM スタンダード', 480000], ['PRD-002', 'データ移行支援', 350000],
                ['PRD-003', '運用サポート年間契約', 240000],
            ] as [$code, $name, $price]) {
                $productIds[$code] = $this->upsertId('products', 'product_code', $code, [
                    'product_name' => $name, 'description' => 'Prime CRM 商品・サービス',
                    'unit_price' => $price, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            $quoteId = $this->upsertId('quotes', 'quote_number', 'QT-2026-0012', [
                'opportunity_id' => $opportunityIds['在庫管理クラウド更新'], 'quote_date' => now()->toDateString(),
                'expiration_date' => now()->addMonth()->toDateString(), 'status' => 'submitted',
                'total_amount' => 1310000, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('quote_items')->updateOrInsert(['quote_id' => $quoteId, 'product_id' => $productIds['PRD-001']], [
                'quantity' => 2, 'unit_price' => 480000, 'amount' => 960000, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('quote_items')->updateOrInsert(['quote_id' => $quoteId, 'product_id' => $productIds['PRD-002']], [
                'quantity' => 1, 'unit_price' => 350000, 'amount' => 350000, 'created_at' => $now, 'updated_at' => $now,
            ]);

            DB::table('campaigns')->updateOrInsert(['campaign_name' => '2026 DX相談会'], [
                'campaign_type' => 'Web', 'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addMonth()->endOfMonth()->toDateString(), 'budget' => 850000,
                'status' => 'in_progress', 'created_at' => $now, 'updated_at' => $now,
            ]);
        });
    }

    private function upsertId(string $table, string $key, mixed $value, array $data): int
    {
        DB::table($table)->updateOrInsert([$key => $value], $data);

        return (int) DB::table($table)->where($key, $value)->value('id');
    }
}
