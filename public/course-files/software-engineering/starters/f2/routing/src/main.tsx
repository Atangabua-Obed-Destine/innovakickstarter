import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.tsx';
import './shop.css';

// TODO 1: give the whole app a router. Import BrowserRouter from 'react-router'
// and wrap <App /> in it:  <BrowserRouter><App /></BrowserRouter>
// BrowserRouter reads the address bar and keeps it in step with the page.
createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
