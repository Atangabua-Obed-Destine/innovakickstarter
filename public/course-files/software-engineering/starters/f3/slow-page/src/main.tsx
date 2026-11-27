import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { OffersPage } from './OffersPage'
import './index.css'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <OffersPage />
  </StrictMode>,
)
