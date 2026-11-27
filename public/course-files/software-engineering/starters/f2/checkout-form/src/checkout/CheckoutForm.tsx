import { useRef, useState, type ChangeEvent, type FormEvent } from 'react';
import type { Branch, Order, OrderResult, PaymentMethod } from '../types.ts';
import { needsPhone, serverErrorsToFields, validateCheckout, type CheckoutValues, type FieldErrors } from './validate.ts';

const METHODS: { code: PaymentMethod; words: string }[] = [
  { code: 'cash', words: 'Cash, at the counter' },
  { code: 'mtn_momo', words: 'MTN Mobile Money' },
  { code: 'orange_money', words: 'Orange Money' },
  { code: 'bank_transfer', words: 'Bank transfer' },
];

interface CheckoutFormProps {
  branches: Branch[];
  initialBranch: string;
  onPlaceOrder: (values: CheckoutValues) => Promise<OrderResult>;
  onCreated: (order: Order, values: CheckoutValues) => void;
  onUseAvailable?: (productId: number, available: number) => void;
}

type Problem = { kind: 'stock'; text: string; productId: number; available: number } | { kind: 'failed'; text: string } | null;

export default function CheckoutForm({ branches, initialBranch, onPlaceOrder, onCreated, onUseAvailable }: CheckoutFormProps) {
  const [values, setValues] = useState<CheckoutValues>({ customer_name: '', phone: '', branch: initialBranch, payment_method: 'cash' });
  const [errors, setErrors] = useState<FieldErrors>({});
  const [problem, setProblem] = useState<Problem>(null);
  const [sending, setSending] = useState(false);

  // A ref is a box React keeps between renders. With ref={...} it holds the element,
  // so we can move the keyboard focus to the first field that needs fixing.
  const nameRef = useRef<HTMLInputElement>(null);
  const phoneRef = useRef<HTMLInputElement>(null);
  const branchRef = useRef<HTMLSelectElement>(null);
  const paymentRef = useRef<HTMLInputElement>(null);

  function focusFirst(found: FieldErrors) {
    if (found.customer_name) nameRef.current?.focus();
    else if (found.phone) phoneRef.current?.focus();
    else if (found.branch) branchRef.current?.focus();
    else if (found.payment_method) paymentRef.current?.focus();
  }

  function handleChange(event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) {
    const { name, value } = event.target;
    setValues({ ...values, [name]: value });
  }

  // TODO 3: what happens when the customer presses "Place order".
  // 1. event.preventDefault(); do nothing if already sending; clear the old problem.
  // 2. found = validateCheckout(values); save it in errors. If there are errors,
  //    focusFirst(found) and stop: nothing is sent.
  // 3. setSending(true), then const result = await onPlaceOrder(values), then setSending(false).
  // 4. Handle EVERY kind of result (TypeScript checks you covered them):
  //    'created' -> onCreated(result.order, values)
  //    'invalid' -> serverErrorsToFields(result.errors) into errors, then focusFirst
  //    'stock'   -> setProblem({ kind: 'stock', text: ..., productId, available }) with what was
  //                 asked for and what is available, e.g. "You asked for 2, and 1 are available."
  //    'failed'  -> setProblem({ kind: 'failed', text: ... }) a calm "try again" message
  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    console.log('TODO 3: handle the submit');
  }

  const describe = (name: keyof FieldErrors) => ({
    'aria-invalid': errors[name] ? true : undefined,
    'aria-describedby': errors[name] ? `${name}-error` : undefined,
  });
  const errorText = (name: keyof FieldErrors) =>
    errors[name] ? <p id={`${name}-error`} className="error-text">{errors[name]}</p> : null;

  return (
    <form className="form" onSubmit={handleSubmit} noValidate aria-label="Checkout">
      {problem && (
        <div className="alert" role="alert">
          <p>{problem.text}</p>
          {problem.kind === 'stock' && problem.available > 0 && onUseAvailable && (
            <button type="button" onClick={() => { onUseAvailable(problem.productId, problem.available); setProblem(null); }}>
              Use {problem.available} instead
            </button>
          )}
        </div>
      )}

      <div className="field">
        <label htmlFor="customer_name">Your name</label>
        <input id="customer_name" name="customer_name" ref={nameRef} autoComplete="name" value={values.customer_name} onChange={handleChange} />
        {/* TODO 4: add {...describe('customer_name')} to the input, and {errorText('customer_name')} here */}
      </div>

      <div className="field">
        <label htmlFor="branch">Collect at</label>
        <select id="branch" name="branch" ref={branchRef} value={values.branch} onChange={handleChange}>
          {branches.map((b) => (
            <option key={b.code} value={b.code}>{b.name}</option>
          ))}
        </select>
      </div>

      <fieldset className="field">
        <legend>How will you pay?</legend>
        {METHODS.map((m, index) => (
          <label key={m.code}>
            <input type="radio" name="payment_method" value={m.code} ref={index === 0 ? paymentRef : undefined} checked={values.payment_method === m.code} onChange={handleChange} />
            {m.words}
          </label>
        ))}
      </fieldset>

      {/* TODO 5: show the phone field ONLY when needsPhone(values.payment_method) */}
      {(
        <div className="field">
          <label htmlFor="phone">Mobile money number</label>
          <p className="hint">The phone that will approve the payment, for example 670 00 00 01. We never ask for your PIN.</p>
          <input id="phone" name="phone" type="tel" inputMode="tel" autoComplete="tel" ref={phoneRef} value={values.phone} onChange={handleChange} />
        </div>
      )}

      {/* TODO 6: disable the button while sending, and say "Placing your order..." on it */}
      <button type="submit">Place order</button>
    </form>
  );
}
