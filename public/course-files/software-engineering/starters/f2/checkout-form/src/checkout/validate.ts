import type { PaymentMethod } from '../types.ts';

export interface CheckoutValues {
  customer_name: string;
  phone: string;
  branch: string;
  payment_method: PaymentMethod;
}

export type FieldName = 'customer_name' | 'phone' | 'branch' | 'payment_method' | 'items';
export type FieldErrors = Partial<Record<FieldName | 'form', string>>;

const OPEN_CODES = ['NKW', 'ML4', 'CAV', 'BAF'];
const METHODS: readonly string[] = ['cash', 'mtn_momo', 'orange_money', 'bank_transfer'];

export function needsPhone(method: PaymentMethod): boolean {
  return method === 'mtn_momo' || method === 'orange_money';
}

// "+237 670 00 00 01" -> "670000001"
export function cleanPhone(text: string): string {
  return text.replace(/[\s-]/g, '').replace(/^\+?237(?=\d{9}$)/, '');
}

// TODO 1: check the form BEFORE anything is sent. Return one message per wrong
// field, and {} when all is well. The rules match the server's (POST /orders):
//   customer_name  2 to 80 characters after trim()
//   branch         one of OPEN_CODES
//   payment_method one of METHODS
//   phone          ONLY when needsPhone(payment_method): cleanPhone(phone) is 9 digits starting with 6
// Your own words for the messages: say what to do, not just "invalid".
export function validateCheckout(values: CheckoutValues): FieldErrors {
  throw new Error(`TODO 1: write validateCheckout (name: ${values.customer_name})`);
}

// TODO 2: turn the server's 422 "errors" (Laravel style) into OUR field messages.
//   { customer_name: ['The customer name field must be at least 2 characters.'] }
//     -> { customer_name: 'The customer name field must be at least 2 characters.' }
// Keys customer_name, branch and payment_method keep their name; any key that starts
// with "items" (like "items.0.quantity") becomes "items"; any other key becomes "form".
// Keep the FIRST message of each list.
export function serverErrorsToFields(errors: Record<string, string[]>): FieldErrors {
  throw new Error(`TODO 2: write serverErrorsToFields (${Object.keys(errors).length} keys)`);
}
