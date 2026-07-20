# Prime CRM 공개 배포 가이드

## InfinityFree 제약

InfinityFree 무료 호스팅은 MySQL 기반이며 PostgreSQL 16, Docker, SSH, 서버 측 Composer/Artisan 실행을 제공하지 않습니다. 따라서 이 저장소의 Laravel + PostgreSQL 구성을 InfinityFree 한 곳에 그대로 배포할 수 없습니다.

## 권장 구성

```text
사용자 브라우저
  └─ HTTPS → Vue 정적 사이트 (InfinityFree 또는 정적 호스트)
    └─ HTTPS API → Laravel 13 컨테이너
                      └─ PostgreSQL 16
```

백엔드 호스트에는 다음 기능이 필요합니다.

- Dockerfile 또는 PHP 8.3 실행
- PostgreSQL 16 연결
- 환경 변수와 HTTPS URL
- 배포 시 `php artisan migrate --force` 실행

## Vue를 InfinityFree에 올리는 경우

1. API가 먼저 공개 HTTPS 주소에서 동작하도록 배포합니다.
2. `frontend/.env.production.example`을 `frontend/.env.production`으로 복사합니다.
3. `VITE_API_BASE_URL`을 Laravel API의 origin으로 설정합니다. 끝에 `/api`는 붙이지 않습니다.
4. Laravel 환경 변수 `CORS_ALLOWED_ORIGINS`에 InfinityFree 프런트 주소를 설정합니다.
5. 아래 명령으로 정적 파일을 생성합니다.

```bash
docker compose run --rm frontend npm run build
```

6. `frontend/dist` 안의 파일만 InfinityFree의 `htdocs`에 업로드합니다. 빌드 시 SPA 새로고침 처리를 위한 `.htaccess`도 함께 복사됩니다.

예시:

```dotenv
# frontend/.env.production
VITE_API_BASE_URL=https://api.example-host.com

# backend production environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example-host.com
CORS_ALLOWED_ORIGINS=https://your-site.infinityfreeapp.com
```

## 공개 전 체크

- `APP_DEBUG=false`
- 강한 DB 비밀번호와 새 `APP_KEY` 사용
- API와 화면 모두 HTTPS 사용
- 데모 계정 비밀번호 변경 또는 인증 기능 추가
- 운영 환경에서는 `SEED_DATABASE=false`
- DB 백업 정책 설정
