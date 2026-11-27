// Self-check for the Forms in React lesson. Run:  npm test
// The tests check WHICH fields are wrong, not your exact words.
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test } from 'vitest';
import App from './App.jsx';
import { NKWEN } from './data/nkwen.js';
import { formatFcfa } from './lib/format.js';
import { validateRequest } from './lib/validate.js';

const GOOD = { business: 'Mbah Provisions', phone: '670 00 00 01', branch: 'NKW', productId: '9', quantity: '20', payment: 'mtn_momo', callMe: true, message: '' };
const wrongFields = (changes) => Object.keys(validateRequest({ ...GOOD, ...changes })).sort();

describe('TODO 1: validateRequest', () => {
  test('good answers give no errors', () => {
    expect(validateRequest(GOOD)).toEqual({});
  });

  test('an empty form: business, phone and product are wrong', () => {
    expect(wrongFields({ business: '', phone: '', productId: '' })).toEqual(['business', 'phone', 'productId']);
  });

  test('business name: 2 to 80 characters after trimming', () => {
    expect(wrongFields({ business: '  A  ' })).toEqual(['business']);
    expect(wrongFields({ business: 'B'.repeat(81) })).toEqual(['business']);
    expect(wrongFields({ business: '  Ma Bih  ' })).toEqual([]);
  });

  test('phone: a Cameroon mobile number, with or without spaces and +237', () => {
    expect(wrongFields({ phone: '+237 670 00 00 01' })).toEqual([]);
    expect(wrongFields({ phone: '670000001' })).toEqual([]);
    expect(wrongFields({ phone: '12345' })).toEqual(['phone']);
    expect(wrongFields({ phone: '770 00 00 01' })).toEqual(['phone']);
  });

  test('quantity: a whole number from 10 to 500', () => {
    for (const ok of ['10', '500', '45']) expect(wrongFields({ quantity: ok }), ok).toEqual([]);
    for (const bad of ['9', '501', '12.5', 'abc', '']) expect(wrongFields({ quantity: bad }), bad).toEqual(['quantity']);
  });

  test('payment must be a known method; the message is optional but short', () => {
    expect(wrongFields({ payment: 'credit' })).toEqual(['payment']);
    expect(wrongFields({ message: 'x'.repeat(301) })).toEqual(['message']);
    expect(wrongFields({ message: 'Please deliver on Saturday.' })).toEqual([]);
  });
});

describe('TODO 2 to 6: the form', () => {
  test('every field is controlled: what I type or choose is what the form holds', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByLabelText('Business name'), 'Mbah Provisions');
    expect(screen.getByLabelText('Business name')).toHaveValue('Mbah Provisions');
    await user.selectOptions(screen.getByLabelText('Branch'), 'BAF');
    expect(screen.getByLabelText('Branch')).toHaveValue('BAF');
    await user.selectOptions(screen.getByLabelText('Product'), '9');
    expect(screen.getByLabelText('Product')).toHaveValue('9');
    await user.clear(screen.getByLabelText('Quantity'));
    await user.type(screen.getByLabelText('Quantity'), '25');
    expect(screen.getByLabelText('Quantity')).toHaveValue(25);
    await user.click(screen.getByLabelText('Orange Money'));
    expect(screen.getByLabelText('Orange Money')).toBeChecked();
    expect(screen.getByLabelText('Cash')).not.toBeChecked();
    await user.click(screen.getByLabelText('Call me back today'));
    expect(screen.getByLabelText('Call me back today')).toBeChecked();
    await user.type(screen.getByLabelText('Message (optional)'), 'Thanks');
    expect(screen.getByLabelText('Message (optional)')).toHaveValue('Thanks');
  });

  test('sending an empty form shows errors linked to their fields', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(screen.getByRole('button', { name: 'Send request' }));
    const business = screen.getByLabelText('Business name');
    expect(business).toHaveAttribute('aria-invalid', 'true');
    expect(business).toHaveAccessibleDescription(/.+/);
    expect(screen.getByLabelText('Product')).toHaveAttribute('aria-invalid', 'true');
    expect(screen.queryByRole('heading', { name: /Thank you/ })).toBeNull();
  });

  test('a good request shows a thank-you with the estimated value, then can start again', async () => {
    const user = userEvent.setup();
    render(<App />);
    const sugar = NKWEN.products.find((p) => p.id === 9);
    await user.type(screen.getByLabelText('Business name'), 'Mbah Provisions');
    await user.type(screen.getByLabelText('Phone number'), '670 00 00 01');
    await user.selectOptions(screen.getByLabelText('Product'), String(sugar.id));
    await user.clear(screen.getByLabelText('Quantity'));
    await user.type(screen.getByLabelText('Quantity'), '20');
    await user.click(screen.getByRole('button', { name: 'Send request' }));
    expect(screen.getByRole('heading', { name: 'Thank you, Mbah Provisions' })).toBeInTheDocument();
    expect(screen.getByText(new RegExp(formatFcfa(20 * sugar.price_fcfa)))).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'Send another request' }));
    expect(screen.getByLabelText('Business name')).toHaveValue('');
  });
});
