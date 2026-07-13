# Prime CRM

Laravel 12 API、Vue 3／Vite フロントエンド、PostgreSQL 16で構成された顧客管理システムです。

## Dockerでの起動方法

Docker DesktopおよびDocker Composeが必要です。初回起動時にPHP／Node.jsの依存パッケージのインストール、Laravelのアプリケーションキー生成、データベースマイグレーションが自動的に実行されます。

```bash
cp .env.example .env
docker compose up --build
```

- Vue 3画面：http://localhost:5173
- Laravel API：http://localhost:8000/api/customers
- PostgreSQL：`127.0.0.1:5433`

バックグラウンドでの起動および停止：

```bash
docker compose up -d --build
docker compose down
```

ログの確認およびLaravelコマンドの実行：

```bash
docker compose logs -f backend frontend
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan test
```

データベースのデータを含め、開発環境を完全に初期化する場合は、次のコマンドを実行してください。

```bash
docker compose down -v
```

ポート番号やデータベース接続情報は、プロジェクトルートの`.env`で変更できます。コンテナ内のLaravelからはPostgreSQLサービスの`postgres:5432`へ接続し、ホストから直接接続する場合は、デフォルトで`127.0.0.1:5433`を使用します。

## ローカル環境で直接実行する場合

PostgreSQLのみをDockerで起動し、LaravelとVue 3をホスト環境で実行することもできます。

```bash
docker compose up -d postgres

cd backend
composer install
php artisan migrate
php artisan serve
```

別のターミナルで次のコマンドを実行します。

```bash
cd frontend
npm install
npm run dev
```

## API一覧

- `GET /api/customers`
- `POST /api/customers`
- `GET /api/customers/{customer}`
- `PUT/PATCH /api/customers/{customer}`
- `DELETE /api/customers/{customer}`
