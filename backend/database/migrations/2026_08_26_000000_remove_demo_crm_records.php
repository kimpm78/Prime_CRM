<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $accountIds = DB::table('accounts')
                ->whereIn('account_code', ['AC-1001', 'AC-1002', 'AC-1003', 'AC-1004', 'AC-1005', 'AC-1006'])
                ->pluck('id');
            $quoteIds = DB::table('quotes')->where('quote_number', 'QT-2026-0012')->pluck('id');

            DB::table('quote_items')->whereIn('quote_id', $quoteIds)->delete();
            DB::table('quotes')->whereIn('id', $quoteIds)->delete();
            DB::table('products')->whereIn('product_code', ['PRD-001', 'PRD-002', 'PRD-003'])->delete();
            DB::table('campaigns')->where('campaign_name', '2026 DX相談会')->delete();
            DB::table('cases')->whereIn('subject', [
                '管理画面にログインできない', 'CSV出力の文字化けについて',
                '月次レポートの抽出条件', 'モバイル表示で項目が欠ける',
            ])->delete();
            DB::table('tasks')->whereIn('title', [
                '要件整理資料の送付', '見積条件の最終確認', '医療情報ガイドライン確認',
                '導入スケジュール調整', 'PoCデータ項目の確認',
            ])->delete();
            DB::table('opportunities')->whereIn('opportunity_name', [
                'DX統合基盤 導入プロジェクト', '在庫管理クラウド更新', '患者ポータル構築支援',
                'マーケティング分析パック', '配送可視化システム', '教育データ活用 PoC',
            ])->delete();
            DB::table('leads')->whereIn('lead_name', [
                'ネクストウェーブ株式会社', '株式会社オービット', '東洋フーズ株式会社',
                'シードデザイン合同会社', '光和テクノロジー',
            ])->delete();
            DB::table('contacts')->whereIn('email', [
                'yamada@example.com', 'takahashi@example.com', 'suzuki@example.com', 'ito@example.com',
            ])->delete();
            DB::table('activity_logs')
                ->where('target_table', 'accounts')
                ->whereIn('target_id', $accountIds)
                ->delete();
            DB::table('accounts')->whereIn('id', $accountIds)->delete();

            $salesUserId = DB::table('users')->where('email', 'sales@prime-crm.test')->value('id');
            if ($salesUserId
                && ! DB::table('tasks')->where('assigned_user_id', $salesUserId)->exists()
                && ! DB::table('activities')->where('user_id', $salesUserId)->exists()) {
                DB::table('users')->where('id', $salesUserId)->delete();
            }
        });
    }

    public function down(): void
    {
        // 削除したデモデータは復元しない。
    }
};
