import { useEffect, useState } from 'react'
import { loadOffers } from './api'
import type { OfferWithProduct } from './api'
import { offerEndText } from './dates'
import { formatFcfa } from './format'
import { OFFERS_END } from './offers'
import PriceHistory from './PriceHistory'
import { makeSales, uniqueCustomers } from './stats'

// The home page: this week's offers at Nkwen Traders.
export function OffersPage() {
  const [offers, setOffers] = useState<OfferWithProduct[]>([])
  const [failed, setFailed] = useState(false)
  const [showHistory, setShowHistory] = useState(false)

  useEffect(() => {
    loadOffers().then(setOffers, () => setFailed(true))
  }, [])

  const customers = uniqueCustomers(makeSales(20000))

  return (
    <main>
      <header className="hero">
        <img src="/photos/hero.png" alt="Fresh palm oil from the West region, at Nkwen Traders" className="hero-photo" />
        <h1>This week's offers</h1>
        <p className="ends">{offerEndText(OFFERS_END)}</p>
        <p>Over {customers.toLocaleString('en-US')} happy customers this month.</p>
      </header>

      {failed && <p role="alert">Sorry, the offers could not load. Check your connection and refresh.</p>}

      <ul className="offers">
        {offers.map((offer) => (
          <li key={offer.productId} className="offer">
            <img src={`/photos/${offer.photo}.png`} alt="" className="offer-photo" />
            <h2>{offer.product.name}</h2>
            <p>
              <span className="price">{formatFcfa(offer.product.price_fcfa)}</span> <s>{formatFcfa(offer.wasFcfa)}</s>
            </p>
            <p className="save">Save {formatFcfa(offer.wasFcfa - offer.product.price_fcfa)}</p>
          </li>
        ))}
      </ul>

      <section className="history">
        <button type="button" onClick={() => setShowHistory(!showHistory)} aria-expanded={showHistory}>
          {showHistory ? 'Hide' : 'Show'} price history
        </button>
        {showHistory && <PriceHistory />}
      </section>
    </main>
  )
}
