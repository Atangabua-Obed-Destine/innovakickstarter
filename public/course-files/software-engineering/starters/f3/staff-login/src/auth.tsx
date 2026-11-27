import { createContext, useContext, useState } from 'react'
import type { ReactNode } from 'react'
import { apiFetch } from './api'

export type Staff = { id: number; name: string; email: string; role: 'cashier' | 'branch_manager' | 'head_office'; branch: string | null }
type LoginAnswer = { token: string; token_type: 'Bearer'; data: Staff }

type Auth = {
  user: Staff | null
  token: string | null
  login: (email: string, password: string) => Promise<Staff>
  logout: (reason?: string) => void
  notice: string // a message for the login page, for example "Your session has ended."
}

const AuthContext = createContext<Auth | null>(null)
const TOKEN_KEY = 'nkwen-staff-token'
const USER_KEY = 'nkwen-staff-user'

// Decide where the token lives (README step 3): memory, sessionStorage or localStorage.
// The tests expect sessionStorage, under the keys above.
export function AuthProvider({ children }: { children: ReactNode }) {
  const [token, setToken] = useState<string | null>(null) // TODO: start from the saved token
  const [user, setUser] = useState<Staff | null>(null) // TODO: start from the saved user
  const [notice, setNotice] = useState('')

  async function login(email: string, password: string): Promise<Staff> {
    // TODO 1 (README step 4): POST /login with apiFetch, save the token and the user, update the state, clear the notice.
    void [apiFetch<LoginAnswer>, password, TOKEN_KEY, USER_KEY]
    throw new Error(`TODO: log ${email} in (README step 4)`)
  }

  function logout(reason = '') {
    // TODO 2 (README step 8): remove the token and the user from storage and state, and keep the reason as the notice.
    setNotice(reason)
  }

  void setToken
  void setUser
  return <AuthContext.Provider value={{ user, token, login, logout, notice }}>{children}</AuthContext.Provider>
}

export function useAuth(): Auth {
  const auth = useContext(AuthContext)
  if (!auth) throw new Error('useAuth must be used inside <AuthProvider>')
  return auth
}
