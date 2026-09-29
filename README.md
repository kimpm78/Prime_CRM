# Prime CRM

Salesforce Pro Suiteの考え方を参考にした、ポートフォリオ向けCRMシステムです。提供された要件定義書・画面構成検討書・DB定義書を基に、Laravel APIとPostgreSQL 16で取引先、リード、商談、活動、タスク、問い合わせ、見積を管理します。

フロントエンドの技術構成、コンポーネント設計、開発コマンドについては、[フロントエンドREADME](frontend/README.md)を参照してください。

## システム構成

- バックエンド: Laravel 13 / PHP 8.3
- データベース: PostgreSQL 16
- フロントエンド: Vue SPA（詳細は[frontend/README.md](frontend/README.md)を参照）
- 実行環境: Docker Compose

```text
Prime_CRM/
├── backend/             # Laravel API
├── frontend/            # Vue SPA
├── docs/                # 配置・運用ドキュメント
└── docker-compose.yml   # ローカル開発環境
```

## 実装範囲

- 営業ダッシュボード: 取引先数、進行中の商談金額、未完了タスク、問い合わせ件数
- 取引先管理: 検索、一覧、登録、詳細、更新、論理削除、サーバー側バリデーション
- リード管理: 検索、一覧、登録、詳細、更新、論理削除、サーバー側バリデーション
- 商談管理: 検索、一覧、登録、詳細、更新、論理削除、サーバー側バリデーション
- タスク管理: 検索、一覧、登録、詳細、更新、論理削除、サーバー側バリデーション
- 通知: 担当タスクの期限リマインド、未読件数、個別既読、一括既読
- 問い合わせ管理: 検索、一覧、登録、詳細、更新、論理削除、完了日時の自動管理
- 設計書に基づく16個のCRMテーブル、外部キー、インデックス、制約
- ロール、商談ステージ、問い合わせステータスのマスターデータ
- ログインセッションによるCRM画面とAPIのアクセス保護
- DBに登録されたデータを使用するダッシュボードと各業務一覧
- 取引先の登録・更新・削除時における`activity_logs`への監査ログ記録

## Dockerでの起動

リポジトリのルートで次のコマンドを実行します。

```bash
cp .env.example .env
docker compose up -d --build
```

初回起動時に、依存パッケージのインストール、Laravelアプリケーションキーの生成、マイグレーション、マスターデータと初期管理者の登録が自動で実行されます。取引先や商談などの業務データは登録されません。

### 初期管理者

ローカル環境の初期ログイン情報は次のとおりです。

```text
メールアドレス: admin@prime-crm.test
パスワード: PrimeCRM2026!
```

公開環境では、起動前に `.env` の `PRIME_CRM_ADMIN_EMAIL` と `PRIME_CRM_ADMIN_PASSWORD` を必ず変更してください。既存管理者のパスワードは、コンテナ再起動や再シードでは上書きされません。

### デモデータ

ポートフォリオ確認用の取引先、商談、タスク、問い合わせなどを投入する場合は、次の専用Seederを手動で実行します。繰り返し実行しても対象データは重複しません。

```bash
docker compose exec backend \
  php artisan db:seed --class=Database\\Seeders\\DemoCrmSeeder
```

デモ営業ユーザーのログイン情報は次のとおりです。

```text
メールアドレス: sales@prime-crm.test
パスワード: password
```

本番環境ではデモデータを投入しないでください。

| サービス | URL・接続先 |
|---|---|
| Webアプリ | http://localhost:5173 |
| Laravel API | http://localhost:8000/api/dashboard |
| PostgreSQL | `127.0.0.1:5433` |

コンテナの状態とログは、次のコマンドで確認できます。

```bash
docker compose ps
docker compose logs -f backend frontend
```

## データベースの確認

マイグレーションの状態と作成済みテーブルを確認します。

```bash
docker compose exec backend php artisan migrate:status
docker compose exec postgres psql -U prime_crm -d prime_crm -c '\dt'
```

取引先と担当者の関連を確認します。

```bash
docker compose exec postgres psql -U prime_crm -d prime_crm -c \
  "SELECT a.account_code, a.account_name, u.name AS owner FROM accounts a LEFT JOIN users u ON u.id = a.owner_user_id WHERE a.is_deleted = false ORDER BY a.id;"
```

商談ステージ別の件数と金額を確認します。

```bash
docker compose exec postgres psql -U prime_crm -d prime_crm -c \
  "SELECT s.stage_name, COUNT(o.id) AS deals, COALESCE(SUM(o.amount), 0) AS amount FROM opportunity_stages s LEFT JOIN opportunities o ON o.stage_id = s.id GROUP BY s.id ORDER BY s.sort_order;"
```

## バックエンドテスト

テストでは開発用PostgreSQLを変更しないように、インメモリSQLiteを明示して実行します。

```bash
docker compose exec \
  -e APP_ENV=testing \
  -e DB_CONNECTION=sqlite \
  -e DB_DATABASE=:memory: \
  backend php artisan test
```

フロントエンドの型チェック、ビルド、Storybookについては、[フロントエンドREADME](frontend/README.md#品質確認)を参照してください。

## API

| メソッド | エンドポイント | 用途 |
|---|---|---|
| `POST` | `/api/auth/login` | ログイン |
| `GET` | `/api/auth/user` | ログインユーザーの取得 |
| `POST` | `/api/auth/logout` | ログアウト |
| `GET` | `/api/dashboard` | ダッシュボード集計の取得 |
| `GET` | `/api/accounts?search=...` | 取引先一覧・検索 |
| `POST` | `/api/accounts` | 取引先登録 |
| `GET` | `/api/accounts/{account}` | 取引先詳細の取得 |
| `PUT/PATCH` | `/api/accounts/{account}` | 取引先更新 |
| `DELETE` | `/api/accounts/{account}` | 取引先の論理削除 |
| `GET` | `/api/leads?search=...` | リード一覧・検索 |
| `POST` | `/api/leads` | リード登録 |
| `GET` | `/api/leads/{lead}` | リード詳細の取得 |
| `PUT/PATCH` | `/api/leads/{lead}` | リード更新 |
| `DELETE` | `/api/leads/{lead}` | リードの論理削除 |
| `GET` | `/api/opportunities?search=...` | 商談一覧・検索 |
| `POST` | `/api/opportunities` | 商談登録 |
| `GET` | `/api/opportunities/{opportunity}` | 商談詳細の取得 |
| `PUT/PATCH` | `/api/opportunities/{opportunity}` | 商談更新 |
| `DELETE` | `/api/opportunities/{opportunity}` | 商談の論理削除 |
| `GET` | `/api/tasks?search=...` | タスク一覧・検索 |
| `POST` | `/api/tasks` | タスク登録 |
| `GET` | `/api/tasks/{task}` | タスク詳細の取得 |
| `PUT/PATCH` | `/api/tasks/{task}` | タスク更新 |
| `DELETE` | `/api/tasks/{task}` | タスクの論理削除 |
| `GET` | `/api/cases?search=...` | 問い合わせ一覧・検索 |
| `POST` | `/api/cases` | 問い合わせ登録 |
| `GET` | `/api/cases/{case}` | 問い合わせ詳細の取得 |
| `PUT/PATCH` | `/api/cases/{case}` | 問い合わせ更新 |
| `DELETE` | `/api/cases/{case}` | 問い合わせの論理削除 |
| `GET` | `/api/notifications` | ログインユーザーの通知一覧・未読件数 |
| `PATCH` | `/api/notifications/{notification}/read` | 通知を既読に変更 |
| `POST` | `/api/notifications/read-all` | すべての通知を既読に変更 |

既存の学習用機能との互換性を保つため、`/api/customers`にもCRUD APIを用意しています。

`/api/auth/login`以外のCRM APIにはログインが必要です。SPAはLaravel SanctumのCSRF CookieとセッションCookieを使用します。

## データベースの初期化

PostgreSQLのボリュームを含めて、すべてのデータを作り直す場合に限り実行してください。

```bash
docker compose down -v
docker compose up -d --build
```

`docker compose down -v`は保存済みのPostgreSQLデータを削除します。必要なデータがないことを確認してから実行してください。

## 公開環境への配置

InfinityFreeではPostgreSQL、Docker、SSHを利用できないため、このシステム全体をそのまま動作させることはできません。

- 推奨構成: LaravelコンテナとPostgreSQLに対応したホスティングサービスへシステム全体を配置する
- 分離構成: フロントエンドの静的ファイルのみInfinityFreeへ配置し、Laravel APIとPostgreSQLは別のホストで動作させる

環境変数、CORS、InfinityFreeへの配置手順は[公開手順書](docs/DEPLOYMENT.md)を参照してください。
