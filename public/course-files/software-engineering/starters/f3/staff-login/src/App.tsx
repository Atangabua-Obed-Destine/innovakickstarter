import { Navigate, Route, Routes } from 'react-router'
import { LoginPage } from './pages/LoginPage'
import { StockPage } from './pages/StockPage'
import { RequireAuth } from './RequireAuth'

// The staff app: a login page, and the stock page behind a login.
export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/stock"
        element={
          <RequireAuth>
            <StockPage />
          </RequireAuth>
        }
      />
      <Route path="*" element={<Navigate to="/stock" replace />} />
    </Routes>
  )
}
