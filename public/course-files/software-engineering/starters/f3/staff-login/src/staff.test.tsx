import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import App from './App'
import { AuthProvider } from './auth'

// These tests describe the finished staff app. Do not change them: change the code until they pass.

const manager = { id: 2, name: 'Mrs. Enow Patience', email: 'manager.nkw@nkwen.test', role: 'branch_manager', branch: 'NKW' }
const rows = [
  { product_id: 1, branch_code: 'NKW', sku: 'NKT-BAK-001', name: 'Bread (loaf)', quantity: 37, reorder_level: 10, status: 'OK' },
  { product_id: 7, branch_code: 'NKW', sku: 'NKT-GRO-007', name: 'Maggi Cubes (pack)', quantity: 3, reorder_level: 15, status: 'LOW' },
  { product_id: 11, branch_code: 'NKW', sku: 'NKT-HOU-011', name: 'Detergent 1kg', quantity: 0, reorder_level: 8, status: 'RESTOCK' },
]

// A pretend practice API. stockStatus decides what GET /branches/.../stock answers.
function fakeApi(stockStatus = 200) {
  const fake = vi.fn(async (url: string | URL | Request, init?: RequestInit) => {
    const path = String(url)
    if (path.endsWith('/login')) {
      const body = JSON.parse(String(init?.body))
      return body.password === 'Nkwen-Demo-2026'
        ? Response.json({ token: 'token-abc', token_type: 'Bearer', data: manager })
        : Response.json({ message: 'These credentials do not match our records.' }, { status: 401 })
    }
    if (stockStatus === 401) return Response.json({ message: 'Unauthenticated.' }, { status: 401 })
    if (stockStatus === 403) return Response.json({ message: 'This action is unauthorized.' }, { status: 403 })
    return Response.json({ data: rows, meta: { count: rows.length } })
  })
  vi.stubGlobal('fetch', fake)
  return fake
}

function appAt(path: string) {
  render(
    <MemoryRouter initialEntries={[path]}>
      <AuthProvider>
        <App />
      </AuthProvider>
    </MemoryRouter>,
  )
}

function loggedIn() {
  sessionStorage.setItem('nkwen-staff-token', 'token-abc')
  sessionStorage.setItem('nkwen-staff-user', JSON.stringify(manager))
}

async function logIn(password = 'Nkwen-Demo-2026') {
  const user = userEvent.setup()
  await user.type(screen.getByLabelText('Email'), 'manager.nkw@nkwen.test')
  await user.type(screen.getByLabelText('Password'), password)
  await user.click(screen.getByRole('button', { name: 'Log in' }))
  return user
}

beforeEach(() => sessionStorage.clear())

describe('Staff login', () => {
  it('the stock page is protected: without a login you see the login form', () => {
    fakeApi()
    appAt('/stock')
    expect(screen.getByRole('heading', { name: 'Staff login' })).toBeInTheDocument()
  })

  it('logging in keeps the token in sessionStorage and opens the stock page', async () => {
    fakeApi()
    appAt('/stock')
    await logIn()

    expect(await screen.findByRole('heading', { name: 'Stock at NKW' })).toBeInTheDocument()
    expect(sessionStorage.getItem('nkwen-staff-token')).toBe('token-abc')
    expect(screen.getByText(/Mrs\. Enow Patience/)).toBeInTheDocument()
  })

  it('a wrong password shows the API message', async () => {
    fakeApi()
    appAt('/login')
    await logIn('wrong-password')

    expect(await screen.findByRole('alert')).toHaveTextContent('These credentials do not match our records.')
    expect(sessionStorage.getItem('nkwen-staff-token')).toBeNull()
  })

  it('the stock request sends the token as a Bearer header', async () => {
    loggedIn()
    const fake = fakeApi()
    appAt('/stock')

    expect(await screen.findByText('Maggi Cubes (pack)')).toBeInTheDocument()
    const call = fake.mock.calls.find(([url]) => String(url).includes('/branches/NKW/stock'))!
    expect((call[1]!.headers as Record<string, string>).Authorization).toBe('Bearer token-abc')
  })

  it('a 401 (expired token) logs out and asks to log in again', async () => {
    loggedIn()
    fakeApi(401)
    appAt('/stock')

    expect(await screen.findByRole('heading', { name: 'Staff login' })).toBeInTheDocument()
    expect(screen.getByRole('status')).toHaveTextContent('Your session has ended. Please log in again.')
    expect(sessionStorage.getItem('nkwen-staff-token')).toBeNull()
  })

  it("a 403 (another branch) shows a clear message and keeps you logged in", async () => {
    loggedIn()
    fakeApi(403)
    appAt('/stock?branch=CAV')

    expect(await screen.findByRole('alert')).toHaveTextContent("You can only see your own branch's stock.")
    expect(sessionStorage.getItem('nkwen-staff-token')).toBe('token-abc')
  })

  it('logging out forgets the token and goes back to the login form', async () => {
    loggedIn()
    fakeApi()
    appAt('/stock')
    const user = userEvent.setup()

    await user.click(await screen.findByRole('button', { name: 'Log out' }))

    expect(screen.getByRole('heading', { name: 'Staff login' })).toBeInTheDocument()
    expect(sessionStorage.getItem('nkwen-staff-token')).toBeNull()
  })
})
