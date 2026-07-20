# Prime CRM 公開・配置ガイド

## InfinityFreeの制約

InfinityFreeの無料ホスティングはMySQLを基盤としており、PostgreSQL 16、Docker、SSH、サーバー上でのComposerおよびArtisanの実行には対応していません。そのため、このリポジトリのLaravelとPostgreSQLによる構成全体を、InfinityFreeだけに配置することはできません。

## 推奨構成

```text
利用者のブラウザ
  └─ HTTPS → Vue静的サイト（InfinityFreeまたは静的ホスティング）
       └─ HTTPS API → Laravel 13コンテナ
                          └─ PostgreSQL 16
```

バックエンドのホスティング環境には、次の機能が必要です。

- Dockerfile、またはPHP 8.3の実行環境
- PostgreSQL 16への接続
- 環境変数の設定
- HTTPS URL
- 配置時における`php artisan migrate --force`の実行

## VueをInfinityFreeへ配置する場合

1. Laravel APIを別のホスティング環境へ配置し、HTTPSの公開URLで動作することを確認します。
2. `frontend/.env.production.example`を`frontend/.env.production`へコピーします。
3. `VITE_API_BASE_URL`にLaravel APIのオリジンを設定します。URLの末尾に`/api`は付けません。
4. Laravelの環境変数`CORS_ALLOWED_ORIGINS`に、InfinityFreeで公開するフロントエンドのオリジンを設定します。
5. 次のコマンドで本番用の静的ファイルを生成します。

```bash
docker compose run --rm frontend npm run build
```

6. `frontend/dist`内のファイルだけをInfinityFreeの`htdocs`へアップロードします。SPAでページを直接開いた場合や再読み込みした場合に対応するため、ビルド時に`.htaccess`もコピーされます。

設定例:

```dotenv
# frontend/.env.production
VITE_API_BASE_URL=https://api.example-host.com

# バックエンドの本番環境
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example-host.com
CORS_ALLOWED_ORIGINS=https://your-site.infinityfreeapp.com
```

## 公開前の確認事項

- `APP_DEBUG=false`になっていること
- 推測されにくいデータベースパスワードと、新しく生成した`APP_KEY`を使用していること
- APIとフロントエンドの両方でHTTPSを使用していること
- デモアカウントのパスワードを変更するか、認証機能を追加していること
- 本番環境では`SEED_DATABASE=false`になっていること
- データベースのバックアップ方針が設定されていること
