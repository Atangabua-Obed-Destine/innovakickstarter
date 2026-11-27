import { useState } from 'react'
import type { FormEvent } from 'react'
import { useLocation, useNavigate } from 'react-router'
import { useAuth } from '../auth'

// The staff login form. After login, go back to the page the person wanted (or /stock).
export function LoginPage() {
  const { login, notice } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [sending, setSending] = useState(false)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setSending(true)
    setError('')
    try {
      await login(email, password)
      navigate((location.state as { from?: string } | null)?.from ?? '/stock', { replace: true })
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Login failed.')
      setSending(false)
    }
  }

  return (
    <main>
      <h1>Staff login</h1>
      {notice && <p role="status">{notice}</p>}
      <form onSubmit={submit}>
        <label htmlFor="email">Email</label>
        <input id="email" type="email" autoComplete="username" value={email} onChange={(e) => setEmail(e.target.value)} required />
        <label htmlFor="password">Password</label>
        <input id="password" type="password" autoComplete="current-password" value={password} onChange={(e) => setPassword(e.target.value)} required />
        {error && <p role="alert">{error}</p>}
        <button type="submit" disabled={sending}>
          {sending ? 'Logging in…' : 'Log in'}
        </button>
      </form>
    </main>
  )
}
