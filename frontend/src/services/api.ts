const baseUrl = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')

const xsrfToken = () => {
  const cookie = document.cookie
    .split('; ')
    .find((item) => item.startsWith('XSRF-TOKEN='))

  return cookie ? decodeURIComponent(cookie.split('=').slice(1).join('=')) : ''
}

export class ApiError extends Error {
  constructor(message: string, public status: number, public errors: Record<string, string[]> = {}) {
    super(message)
  }
}

export async function api<T>(path: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')
  headers.set('Content-Type', 'application/json')

  const method = (options.method ?? 'GET').toUpperCase()
  if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
    const token = xsrfToken()
    if (token) headers.set('X-XSRF-TOKEN', token)
  }

  const response = await fetch(`${baseUrl}${path}`, {
    ...options,
    credentials: 'include',
    headers,
  })
  const payload = response.status === 204 ? null : await response.json().catch(() => null)
  if (!response.ok) throw new ApiError(payload?.message ?? '通信に失敗しました。', response.status, payload?.errors)
  return payload as T
}

export async function prepareCsrf(): Promise<void> {
  const response = await fetch(`${baseUrl}/sanctum/csrf-cookie`, {
    credentials: 'include',
    headers: { Accept: 'application/json' },
  })

  if (!response.ok) throw new ApiError('ログインの準備に失敗しました。', response.status)
}
