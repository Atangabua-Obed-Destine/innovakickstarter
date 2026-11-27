// Self-check for Forms & Validation for Checkout. Run:  npm test  and  npm run typecheck
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test } from 'vitest';
import App from './App.tsx';
import PaymentScreen from './checkout/PaymentScreen.tsx';
import { serverErrorsToFields, validateCheckout, type CheckoutValues } from './checkout/validate.ts';
import type { Order } from './types.ts';

const GOOD: CheckoutValues = { customer_name: 'Mbah Clarisse', phone: '', branch: 'CAV', payment_method: 'cash' };
const wrong = (changes: Partial<CheckoutValues>) => Object.keys(validateCheckout({ ...GOOD, ...changes })).sort();

describe('TODO 1: validateCheckout', () => {
  test('a good cash order has no errors; a phone is not needed for cash', () => {
    expect(validateCheckout(GOOD)).toEqual({});
  });
  test('name: 2 to 80 characters after trimming', () => {
    expect(wrong({ customer_name: '   ' })).toEqual(['customer_name']);
    expect(wrong({ customer_name: ' A ' })).toEqual(['customer_name']);
    expect(wrong({ customer_name: 'x'.repeat(81) })).toEqual(['customer_name']);
  });
  test('branch must be open; Limbe is not', () => {
    expect(wrong({ branch: 'LMB' })).toEqual(['branch']);
  });
  test('mobile money needs a Cameroon mobile number', () => {
    expect(wrong({ payment_method: 'mtn_momo' })).toEqual(['phone']);
    expect(wrong({ payment_method: 'orange_money', phone: '+237 690 00 00 02' })).toEqual([]);
    expect(wrong({ payment_method: 'mtn_momo', phone: '12 34' })).toEqual(['phone']);
    expect(wrong({ payment_method: 'bank_transfer' })).toEqual([]);
  });
});

describe('TODO 2: serverErrorsToFields', () => {
  test('maps Laravel keys to our fields, first message only', () => {
    expect(serverErrorsToFields({ customer_name: ['Too short.', 'Second'], 'items.0.quantity': ['At least 1.'], other: ['Odd.'] }))
      .toEqual({ customer_name: 'Too short.', items: 'At least 1.', form: 'Odd.' });
  });
});

async function fillAndSend(user: ReturnType<typeof userEvent.setup>, name: string) {
  await user.type(screen.getByLabelText('Your name'), name);
  await user.click(screen.getByRole('button', { name: 'Place order' }));
}

describe('TODO 3 to 6: the checkout form', () => {
  test('nothing is sent while the form has errors; the first wrong field gets the focus', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(screen.getByRole('button', { name: 'Place order' }));
    const name = screen.getByLabelText('Your name');
    expect(name).toHaveAttribute('aria-invalid', 'true');
    expect(name).toHaveAccessibleDescription(/.+/);
    expect(name).toHaveFocus();
  });

  test('the phone field appears only for mobile money', async () => {
    const user = userEvent.setup();
    render(<App />);
    expect(screen.queryByLabelText('Mobile money number')).toBeNull();
    await user.click(screen.getByLabelText('Orange Money'));
    expect(screen.getByLabelText('Mobile money number')).toBeInTheDocument();
  });

  test('the button is disabled while sending', async () => {
    const user = userEvent.setup();
    render(<App />);
    await fillAndSend(user, 'Mbah Clarisse');
    expect(screen.getByRole('button', { name: 'Placing your order...' })).toBeDisabled();
    await screen.findByRole('heading', { name: /is booked/ });
  });

  test('422: the server\'s message appears under the right field', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.selectOptions(screen.getByLabelText('Server answer (practice)'), 'invalid');
    await fillAndSend(user, 'Mbah Clarisse');
    expect(await screen.findByText('The customer name field must be at least 2 characters.')).toBeInTheDocument();
    expect(screen.getByLabelText('Your name')).toHaveAttribute('aria-invalid', 'true');
  });

  test('409: says what was asked for and what is available, and offers to use it', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.selectOptions(screen.getByLabelText('Server answer (practice)'), 'stock');
    await fillAndSend(user, 'Mbah Clarisse');
    const alert = await screen.findByRole('alert');
    expect(alert).toHaveTextContent('asked for 2');
    expect(alert).toHaveTextContent('1 are available');
    await user.click(within(alert).getByRole('button', { name: 'Use 1 instead' }));
    expect(screen.getByText('1 x Rice 50kg')).toBeInTheDocument();
  });

  test('500: a calm message, and the customer can try again', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.selectOptions(screen.getByLabelText('Server answer (practice)'), 'failed');
    await fillAndSend(user, 'Mbah Clarisse');
    expect(await screen.findByRole('alert')).toHaveTextContent(/try again/i);
    expect(screen.getByRole('button', { name: 'Place order' })).toBeEnabled();
  });

  test('201 with cash: pay at the counter', async () => {
    const user = userEvent.setup();
    render(<App />);
    await fillAndSend(user, 'Mbah Clarisse');
    expect(await screen.findByText(/at the counter when you collect at Commercial Avenue/)).toBeInTheDocument();
  });
});

describe('TODO 7: the simulated mobile-money screen', () => {
  const order: Order = { id: 'ORD-PRACTICE', branch: 'CAV', customer_name: 'Mbah Clarisse', payment_method: 'mtn_momo', status: 'awaiting_payment', items: [], total_fcfa: 78610, created_at: '2026-12-03T10:15:00+01:00' };

  test('request, approve on the phone, paid', async () => {
    const user = userEvent.setup();
    render(<PaymentScreen order={order} method="mtn_momo" phone="670000001" delayMs={10} />);
    await user.click(screen.getByRole('button', { name: 'Send payment request to 670 00 00 01' }));
    expect(screen.getByRole('status')).toHaveTextContent(/Never type your PIN/);
    await user.click(screen.getByRole('button', { name: 'Customer approves' }));
    expect(await screen.findByText(/Payment received \(simulated\)/)).toBeInTheDocument();
  });

  test('declined: explains why and lets the customer try again', async () => {
    const user = userEvent.setup();
    render(<PaymentScreen order={order} method="orange_money" phone="690000002" delayMs={10} />);
    await user.click(screen.getByRole('button', { name: /Send payment request/ }));
    await user.click(screen.getByRole('button', { name: 'Customer declines' }));
    expect(await screen.findByText(/Payment not completed/)).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'Try again' }));
    expect(screen.getByRole('button', { name: /Send payment request/ })).toBeInTheDocument();
  });

  test('there is never a PIN field', async () => {
    const user = userEvent.setup();
    render(<PaymentScreen order={order} method="mtn_momo" phone="670000001" delayMs={10} />);
    await user.click(screen.getByRole('button', { name: /Send payment request/ }));
    expect(screen.queryByRole('textbox')).toBeNull();
    expect(screen.queryByLabelText(/PIN/i)).toBeNull();
  });
});
