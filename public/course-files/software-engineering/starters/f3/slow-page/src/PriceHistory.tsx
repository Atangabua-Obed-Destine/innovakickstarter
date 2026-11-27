import { useEffect, useRef } from 'react'
import Chart from 'chart.js/auto'

// Rice 50kg shelf price over the last 12 months (FCFA, VAT included).
const MONTHS = ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov']
const PRICES = [37200, 37200, 37800, 38100, 38500, 38500, 38900, 38500, 38500, 38200, 38500, 36005]

// A line chart. Only head office opens it, by pressing "Show price history".
export default function PriceHistory() {
  const canvas = useRef<HTMLCanvasElement>(null)

  useEffect(() => {
    if (!canvas.current) return
    const chart = new Chart(canvas.current, {
      type: 'line',
      data: { labels: MONTHS, datasets: [{ label: 'Rice 50kg (FCFA)', data: PRICES, borderColor: '#b45309' }] },
      options: { animation: false },
    })
    return () => chart.destroy()
  }, [])

  return (
    <figure>
      <canvas ref={canvas} width="400" height="200" role="img" aria-label="Rice 50kg price over 12 months: from 37,200 to 36,005 FCFA, highest 38,900 in June" />
      <figcaption>Rice 50kg: shelf price, last 12 months</figcaption>
    </figure>
  )
}
