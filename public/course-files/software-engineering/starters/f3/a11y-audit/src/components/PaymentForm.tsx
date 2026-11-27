import { useState } from 'react'
import type { FormEvent } from 'react'
import { formatFcfa } from '../format'

type Props = { total: number }
type Method = 'mtn_momo' | 'orange_money' | 'cash'

// A Cameroon mobile number has 9 digits and starts with 6, for example 677123456.
export function isMobileNumber(text: string): boolean {
  return /^6\d{8}$/.test(text.replace(/\s/g, ''))
}

// Name, payment method and phone number. The payment itself is simulated.
export function PaymentForm({ total }: Props) {
  const [name, setName] = useState('')
  const [phone, setPhone] = useState('')
  const [method, setMethod] = useState<Method>('mtn_momo')
  const [error, setError] = useState('')
  const [done, setDone] = useState('')

  function submit(event: FormEvent) {
    event.preventDefault()
    if (name.trim() === '') {
      setError('Enter your name.')
    } else if (method !== 'cash' && !isMobileNumber(phone)) {
      setError('Enter your MTN or Orange Money number: 9 digits starting with 6.')
    } else {
      setError('')
      setDone(
        method === 'cash'
          ? `Thank you, ${name}. Pay ${formatFcfa(total)} at the counter when you collect.`
          : `Thank you, ${name}. Approve the payment of ${formatFcfa(total)} on your phone.`,
      )
    }
  }

  if (done) return <p>{done}</p>

  return (
    <form onSubmit={submit} noValidate>
      <input type="text" placeholder="Your name" value={name} onChange={(e) => setName(e.target.value)} />

      <p>Payment method</p>
      <input type="radio" name="method" checked={method === 'mtn_momo'} onChange={() => setMethod('mtn_momo')} /> MTN Mobile Money
      <br />
      <input type="radio" name="method" checked={method === 'orange_money'} onChange={() => setMethod('orange_money')} /> Orange Money
      <br />
      <input type="radio" name="method" checked={method === 'cash'} onChange={() => setMethod('cash')} /> Cash at the counter

      <input type="tel" placeholder="Phone number (MTN or Orange)" value={phone} onChange={(e) => setPhone(e.target.value)} />
      <p className="hint">Example: 6 77 12 34 56</p>

      {error && <p className="error">{error}</p>}

      <button type="submit" className="pay">
        Pay {formatFcfa(total)}
      </button>
    </form>
  )
}
