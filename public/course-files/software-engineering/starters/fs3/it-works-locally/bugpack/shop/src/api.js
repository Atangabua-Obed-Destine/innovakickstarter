// The shop's api.js as it was on the day of the incident. Copy it over your own src/api.js on the rehearsal branch.

// Where the API is. Falls back to the shop's own address when nothing is set.
export const API_URL = import.meta.env.VITE_API_URL ?? ''

export function apiUrl(path) {
  return `${API_URL}/api/${path.replace(/^\/+/, '')}`
}

export class ApiError extends Error {
  constructor(status, data) {
    super(data?.message || `The API answered ${status}`)
    this.status = status
    this.data = data
  }
}

export async function request(path, { method = 'GET', body, token, headers: extra = {} } = {}) {
  const headers = { Accept: 'application/json', ...extra }
  if (body !== undefined) headers['Content-Type'] = 'application/json'
  if (token) headers.Authorization = `Bearer ${token}`

  let response
  try {
    response = await fetch(apiUrl(path), { method, headers, body: body === undefined ? undefined : JSON.stringify(body) })
  } catch {
    throw new Error('Could not reach the shop server. Check your connection and try again.')
  }

  if (response.status === 204) return null
  const data = await response.json()
  if (!response.ok) throw new ApiError(response.status, data)
  return data
}

export const getProducts = () => request('/products').then((json) => json.data)
export const getBranches = () => request('/branches').then((json) => json.data)
export const getStock = (branch) => request(`/branches/${branch}/stock`).then((json) => json.data)
