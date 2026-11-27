// Every call to the Nkwen API goes through apiFetch.
export const API_URL: string = (import.meta.env.VITE_API_URL ?? 'https://innovakickstarter.com/api/course/nkwen').replace(/\/+$/, '')

// An error that remembers the HTTP status, so pages can treat 401 and 403 differently.
export class ApiError extends Error {
  status: number
  constructor(status: number, message: string) {
    super(message)
    this.status = status
  }
}

type Options = { method?: string; body?: unknown; token?: string | null }

export async function apiFetch<T>(path: string, { method = 'GET', body, token }: Options = {}): Promise<T> {
  const headers: Record<string, string> = { Accept: 'application/json' }
  if (body !== undefined) headers['Content-Type'] = 'application/json'
  if (token) headers.Authorization = `Bearer ${token}`

  let response: Response
  try {
    response = await fetch(`${API_URL}${path}`, { method, headers, body: body === undefined ? undefined : JSON.stringify(body) })
  } catch {
    throw new ApiError(0, 'Could not reach the server. Check your connection and try again.')
  }
  const data = await response.json().catch(() => null)
  if (!response.ok) {
    const message = response.status === 429 ? 'Too many tries. Wait a minute, then try again.' : (data?.message ?? `The server answered ${response.status}.`)
    throw new ApiError(response.status, message)
  }
  return data as T
}
