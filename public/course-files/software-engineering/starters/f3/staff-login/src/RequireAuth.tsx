import type { ReactNode } from 'react'

// A protected route: without a logged-in member of staff, go to /login (README step 6).
// TODO 3: read user from useAuth(). If there is none, return <Navigate to="/login" replace state={{ from: ... }} />
//         (from react-router), remembering the address with useLocation().
export function RequireAuth({ children }: { children: ReactNode }) {
  return children
}
