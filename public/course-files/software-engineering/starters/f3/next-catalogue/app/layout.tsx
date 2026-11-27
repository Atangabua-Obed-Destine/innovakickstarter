import type { Metadata } from 'next'
import Link from 'next/link'
import './globals.css'

// Every page gets this title unless it sets its own.
export const metadata: Metadata = {
  title: 'Nkwen Traders catalogue',
  description: 'Food and household goods at Nkwen Traders, Bamenda and Bafoussam. Prices in FCFA, VAT included.',
}

// The layout wraps every page: the header and footer are written once.
export default function RootLayout({ children }: LayoutProps<'/'>) {
  return (
    <html lang="en">
      <body>
        <header>
          <Link href="/">Nkwen Traders</Link>
        </header>
        {children}
        <footer>Prices include VAT (19.25%).</footer>
      </body>
    </html>
  )
}
