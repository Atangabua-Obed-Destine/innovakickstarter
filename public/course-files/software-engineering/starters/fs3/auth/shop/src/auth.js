// Logging staff in and out. The token is the "key" to the API: keep it safe.
import { request } from './api'

const KEY = 'nkwen-token'

// We keep the token in localStorage so a refresh does not log the cashier out (see the lesson for the risks).
export const savedToken = () => localStorage.getItem(KEY)
export const saveToken = (token) => localStorage.setItem(KEY, token)
export const forgetToken = () => localStorage.removeItem(KEY)

// POST /api/login answers {token, data: {id, name, email, role, branch}}. Return { user, token }.
export async function login(email, password) {
  // TODO 1: call request('/login', { method: 'POST', body: {...} }), save the token, return { user, token }.
  throw new Error('TODO: log in (README step 6)')
}

// POST /api/register makes a cashier account (201, no token), so log in straight after.
export async function register(name, email, password, branch) {
  // TODO 2: send name, email, password, password_confirmation and branch; then return login(email, password).
  throw new Error('TODO: sign up (README step 6)')
}

// Who is logged in? null when there is no token, or the token no longer works (401).
export async function currentUser(token) {
  // TODO 3: GET /me with the token. On a 401, forget the token and return null.
  return null
}

export async function logout(token) {
  try {
    await request('/logout', { method: 'POST', token })
  } finally {
    forgetToken()
  }
}
