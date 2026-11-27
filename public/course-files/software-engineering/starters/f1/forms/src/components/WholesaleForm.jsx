import { useState } from 'react';
import { formatFcfa } from '../lib/format.js';
import { cleanPhone, validateRequest } from '../lib/validate.js';

const EMPTY = { business: '', phone: '', branch: 'NKW', productId: '', quantity: '10', payment: 'cash', callMe: false, message: '' };
const PAYMENT_WORDS = { cash: 'Cash', mtn_momo: 'MTN Mobile Money', orange_money: 'Orange Money', bank_transfer: 'Bank transfer' };

// Ready: shows a field's error under it. The id links it to the input (aria-describedby).
function FieldError({ name, errors }) {
  if (!errors[name]) return null;
  return <p id={`${name}-error`} className="error-text">{errors[name]}</p>;
}

// Ready: the props every input needs so everyone, including screen readers, gets its error.
function errorProps(name, errors) {
  return { 'aria-invalid': errors[name] ? true : undefined, 'aria-describedby': errors[name] ? `${name}-error` : undefined };
}

export default function WholesaleForm({ products, branches }) {
  // TODO 2: keep ALL the answers in ONE state object that starts as EMPTY,
  // plus two more pieces of state: errors (starts as {}) and sent (starts as null).
  const values = EMPTY;
  const errors = {};
  const sent = null;

  // TODO 3: one change handler for every field. Each input has a "name" that
  // matches a key in values. Make a NEW object: { ...values, [name]: newValue }.
  // For the checkbox the new value is event.target.checked; for the others it is
  // event.target.value.
  function handleChange(event) {
    console.log('TODO 3: handle a change in', event.target.name);
  }

  // TODO 4: stop the browser reloading the page (event.preventDefault()), then
  // check the answers with validateRequest(values) and save the result in errors.
  // When there are no errors, save the answers in sent.
  function handleSubmit(event) {
    event.preventDefault();
  }

  if (sent) {
    // TODO 6: return a <section className="panel" aria-labelledby="sent-title"> with
    // - <h2 id="sent-title">Thank you, {business name}</h2>
    // - a sentence with the cleaned phone number, the quantity, the product name and the
    //   estimated value (quantity x price_fcfa, with formatFcfa). Prices include VAT.
    // - a "Send another request" button that puts values, errors and sent back to the start.
    return <p>TODO 6: thank-you message</p>;
  }

  return (
    <form className="form" onSubmit={handleSubmit} noValidate>
      {/* These two fields are fully wired: copy their pattern for the others. */}
      <div className="field">
        <label htmlFor="business">Business name</label>
        <input id="business" name="business" value={values.business} onChange={handleChange} {...errorProps('business', errors)} />
        <FieldError name="business" errors={errors} />
      </div>

      <div className="field">
        <label htmlFor="phone">Phone number</label>
        <p className="hint" id="phone-hint">A Cameroon mobile number, for example 670 00 00 01.</p>
        <input id="phone" name="phone" type="tel" inputMode="tel" value={values.phone} onChange={handleChange} {...errorProps('phone', errors)} />
        <FieldError name="phone" errors={errors} />
      </div>

      {/* TODO 5: wire up every field below: value (or checked) from values,
          onChange={handleChange}, and the error props and <FieldError> where a field can be wrong. */}
      <div className="field">
        <label htmlFor="branch">Branch</label>
        <select id="branch" name="branch">
          {branches.map((b) => (
            <option key={b.code} value={b.code}>{b.name}</option>
          ))}
        </select>
      </div>

      <div className="field">
        <label htmlFor="productId">Product</label>
        <select id="productId" name="productId">
          <option value="">Choose a product</option>
          {products.map((p) => (
            <option key={p.id} value={p.id}>{p.name} ({formatFcfa(p.price_fcfa)} / {p.unit})</option>
          ))}
        </select>
      </div>

      <div className="field">
        <label htmlFor="quantity">Quantity</label>
        <input id="quantity" name="quantity" type="number" min="10" max="500" />
      </div>

      <fieldset className="field">
        <legend>How will you pay?</legend>
        {Object.entries(PAYMENT_WORDS).map(([code, words]) => (
          <label key={code}>
            {/* A radio is checked when values.payment === code */}
            <input type="radio" name="payment" value={code} />
            {words}
          </label>
        ))}
      </fieldset>

      <div className="field">
        <label>
          <input type="checkbox" name="callMe" />
          Call me back today
        </label>
      </div>

      <div className="field">
        <label htmlFor="message">Message (optional)</label>
        <textarea id="message" name="message" rows="3" />
      </div>

      <button type="submit">Send request</button>
    </form>
  );
}
