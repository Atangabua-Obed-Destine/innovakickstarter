import { useEffect, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router'
import { ApiError, apiFetch } from '../api'
import { useAuth } from '../auth'

type Row = { product_id: number; branch_code: string; sku: string; name: string; quantity: number; reorder_level: number; status: 'OK' | 'LOW' | 'RESTOCK' }
const BRANCHES = ['NKW', 'ML4', 'CAV', 'BAF']

// The manager's page: stock at one branch, from GET /branches/{code}/stock (needs a token).
export function StockPage() {
  const { user, token, logout } = useAuth()
  const navigate = useNavigate()
  const [params, setParams] = useSearchParams()
  const branch = params.get('branch') ?? user?.branch ?? 'NKW'
  const [rows, setRows] = useState<Row[] | null>(null)
  const [error, setError] = useState('')

  useEffect(() => {
    let ignore = false
    setRows(null)
    setError('')
    // TODO 4 (README step 5): send the token, so the API knows who is asking.
    apiFetch<{ data: Row[] }>(`/branches/${branch}/stock`)
      .then((json) => !ignore && setRows(json.data))
      .catch((e: unknown) => {
        if (ignore) return
        // TODO 5 (README step 7): on 401 log out with 'Your session has ended. Please log in again.' and go to /login;
        //         on 403 show "You can only see your own branch's stock."
        void ApiError
        void navigate
        setError(e instanceof Error ? e.message : 'Could not load the stock.')
      })
    return () => {
      ignore = true
    }
  }, [branch, token])

  return (
    <main>
      <header className="bar">
        <p>
          Logged in as {user?.name} ({user?.role.replace('_', ' ')})
        </p>
        <button type="button" onClick={() => logout('You are logged out.')}>
          Log out
        </button>
      </header>
      <h1>Stock at {branch}</h1>
      {user?.role === 'head_office' && (
        <>
          <label htmlFor="branch">Branch</label>{' '}
          <select id="branch" value={branch} onChange={(e) => setParams({ branch: e.target.value })}>
            {BRANCHES.map((code) => (
              <option key={code}>{code}</option>
            ))}
          </select>
        </>
      )}
      {error && <p role="alert">{error}</p>}
      {!error && !rows && <p role="status">Loading the stock…</p>}
      {rows && (
        <table>
          <caption>{rows.filter((r) => r.status !== 'OK').length} products need attention</caption>
          <thead>
            <tr>
              <th scope="col">Product</th>
              <th scope="col">Quantity</th>
              <th scope="col">Reorder level</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((r) => (
              <tr key={r.product_id}>
                <td>{r.name}</td>
                <td>{r.quantity}</td>
                <td>{r.reorder_level}</td>
                <td className={`status-${r.status.toLowerCase()}`}>{r.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </main>
  )
}
