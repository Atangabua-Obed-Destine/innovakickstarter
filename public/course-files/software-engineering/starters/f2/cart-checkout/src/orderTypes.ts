import type { CheckoutValues } from './checkout/validate.ts';
import type { Order } from './types.ts';

// The order the customer just placed, kept in App so the payment and
// confirmation pages can show it. (The practice API saves nothing, so there is
// no GET /orders/{id} to load it again: after a refresh it is gone, on purpose.)
export interface PlacedOrder {
  order: Order;
  values: CheckoutValues;
}
