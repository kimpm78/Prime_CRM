# Prime CRM フロントエンド

Prime CRMのフロントエンドは、Vue 3とTypeScriptで構築したSPAです。視認性を重視した管理画面として、Volt UIを基盤にAtomic Designでコンポーネントを整理しています。

## 技術構成

- Vue 3.5
- TypeScript 5.9
- Vite 8
- Tailwind CSS 4
- Volt UI / PrimeVue unstyled
- Lucide Vue Next
- Pinia
- Vue Router
- Storybook 10
- Node.js 22

## ディレクトリ構成

```text
frontend/
├── .storybook/          # Storybook設定
├── public/              # 静的ファイル・SPA用.htaccess
└── src/
    ├── components/
    │   ├── ui/          # Volt UIのソースコンポーネント
    │   ├── atoms/       # 最小単位の共通UI
    │   ├── molecules/   # 入力項目などのUI組み合わせ
    │   └── organisms/   # CRM業務単位の複合UI
    ├── router/          # 画面ルーティング
    ├── services/        # Laravel APIとの通信
    ├── stores/          # Piniaストア
    ├── types/           # TypeScript型定義
    └── views/           # ページコンポーネント
```

コンポーネントは次の階層で管理します。

```text
src/components/
├── ui/
│   ├── Button.vue
│   ├── DataTable.vue
│   ├── Dialog.vue
│   ├── Drawer.vue
│   ├── InputText.vue
│   ├── SecondaryButton.vue
│   ├── Select.vue
│   └── Tag.vue
├── atoms/
│   ├── AppButton.vue
│   ├── AppInput.vue
│   ├── AppBadge.vue
│   └── AppIcon.vue
├── molecules/
│   ├── FormField.vue
│   ├── SearchBox.vue
│   └── StatusSelect.vue
└── organisms/
    ├── CustomerTable.vue
    ├── CustomerForm.vue
    └── AppSidebar.vue
```

## コンポーネント設計ルール

- `ui`: Volt UIから取得した基盤コンポーネントを配置します。業務固有の処理は追加しません。
- `atoms`: ボタン、入力、バッジ、アイコンなど、アプリ全体で再利用する最小単位です。
- `molecules`: ラベル付き入力、検索欄、ステータス選択など、複数のAtomを組み合わせます。
- `organisms`: 取引先テーブル、取引先フォーム、サイドバーなど、CRMの業務単位で構成します。
- `views`: データ取得と画面レイアウトを担当し、可能な限り業務UIはOrganismへ分離します。

Volt UIのソースは`src/components/ui`で管理し、見た目や業務要件への適合はAtoms以降のラッパーで行います。これにより、Volt UIの更新とアプリ固有の変更を分離します。

## Dockerでの開発

リポジトリのルートで全サービスを起動します。

```bash
cp .env.example .env
docker compose up -d --build
```

フロントエンドは次のURLで確認できます。

- アプリケーション: http://localhost:5173
- Storybook: http://localhost:6006

フロントエンドコンテナのログを確認する場合は、次のコマンドを使用します。

```bash
docker compose logs -f frontend
```

## ローカルでの開発

Node.js 22を使用します。Laravel APIを別途起動した状態で、次のコマンドを実行してください。

```bash
cd frontend
npm install
npm run dev
```

開発サーバーでは`/api`へのリクエストをLaravelへ転送します。Docker環境では`VITE_API_PROXY_TARGET`がComposeから設定されます。

## 環境変数

本番ビルド用のサンプルをコピーして、Laravel APIの公開URLを設定します。

```bash
cp .env.production.example .env.production
```

```dotenv
VITE_API_BASE_URL=https://api.example.com
```

`VITE_API_BASE_URL`の末尾に`/api`は付けません。アプリ側でAPIパスを付加します。環境変数を変更した場合は、再度ビルドしてください。

## npmスクリプト

| コマンド | 用途 |
|---|---|
| `npm run dev` | Vite開発サーバーを起動 |
| `npm run type-check` | Vue・TypeScriptの型チェック |
| `npm run build` | 本番用ファイルを`dist`へ生成 |
| `npm run preview` | 本番ビルドをローカルで確認 |
| `npm run storybook` | Storybook開発サーバーを起動 |
| `npm run build-storybook` | Storybookの静的ファイルを生成 |

Dockerコンテナ内で実行する場合は、各コマンドの先頭に`docker compose exec frontend`を付けます。

## Storybook

Atoms、Molecules、Organismsの表示と状態を、アプリケーションから独立して確認できます。

```bash
docker compose exec frontend npm run storybook
```

静的なStorybookを生成する場合は、次のコマンドを実行します。

```bash
docker compose exec frontend npm run build-storybook
```

## 状態管理とルーティング

- `src/stores/crm.ts`: ダッシュボードと取引先データの状態、読み込み状態、エラー状態を管理します。
- `src/services`: Laravel APIへのリクエスト処理を集約します。
- `src/router/index.ts`: Vue Routerのルートを定義し、ページコンポーネントを遅延読み込みします。
- `src/types`: APIレスポンスとフォームデータの型を定義します。

## 品質確認

変更後は、少なくとも型チェックと本番ビルドを実行します。

```bash
docker compose exec frontend npm run type-check
docker compose exec frontend npm run build
```

コンポーネントを追加・変更した場合は、Storybookの静的ビルドも確認します。

```bash
docker compose exec frontend npm run build-storybook
```

## InfinityFreeへの配置

InfinityFreeには、Viteが生成した静的ファイルのみを配置します。Laravel APIとPostgreSQLは、別のホスティング環境で動作させる必要があります。

1. `.env.production`の`VITE_API_BASE_URL`にLaravel APIのHTTPS URLを設定します。
2. `npm run build`を実行します。
3. `dist`内のファイルをInfinityFreeの`htdocs`へアップロードします。
4. 直接URLを開いた場合もVue Routerが動作することを確認します。
5. Laravel側の`CORS_ALLOWED_ORIGINS`にInfinityFreeの公開ドメインを設定します。

`public/.htaccess`はビルド時に`dist/.htaccess`へコピーされ、SPAのルーティングに使用されます。システム全体の配置方針は[公開手順書](../docs/DEPLOYMENT.md)を参照してください。
