// Every call from the shop to your Laravel API goes through this file.

// TODO 1: read the API's address from the environment variable VITE_API_URL (import.meta.env.VITE_API_URL).
//         If it is not set, use 'http://127.0.0.1:8000'. Remove any slash at the end.
export const API_URL = 'TODO'

// apiUrl('/products') -> 'http://127.0.0.1:8000/api/products'
// TODO 2: it must work with or without the first "/", and never make "/api/api".
export function apiUrl(path) {
  throw new Error(`TODO: build the address for ${path} (README step 4)`)
}

// An error that remembers the HTTP status and the JSON the API sent, for example a 422 with its errors.
export class ApiError extends Error {
  constructor(status, data) {
    super(data?.message || `The API answered ${status}`)
    this.status = status
    this.data = data
  }
}

// Sends one request and returns the JSON. Throws ApiError for 4xx/5xx, and a clear Error when the network fails.
export async function request(path, { method = 'GET', body, token } = {}) {
  const headers = { Accept: 'application/json' }
  if (body !== undefined) headers['Content-Type'] = 'application/json'
  if (token) headers.Authorization = `Bearer ${token}`

  // TODO 3: call fetch(apiUrl(path), ...) with the method, the headers and the body as JSON text.
  //  - If fetch itself throws (no network), throw new Error('Could not reach the shop server. Check your connection and try again.')
  //  - If the status is 204, return null.
  //  - Read the JSON. If response.ok is false, throw new ApiError(response.status, data).
  throw new Error(`TODO: send ${method} ${path} (README step 5)`)
}

export const getProducts = () => request('/products').then((json) => json.data)
export const getBranches = () => request('/branches').then((json) => json.data)
export const getStock = (branch) => request(`/branches/${branch}/stock`).then((json) => json.data)
