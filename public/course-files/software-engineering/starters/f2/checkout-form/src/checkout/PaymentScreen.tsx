import { useState } from 'react';
import { formatFcfa } from '../lib/format.ts';
import type { Order } from '../types.ts';
import { checkStatus, formatPhone, type PhoneAnswer } from './momo.ts';

type Step =
  | { step: 'ready' }
  | { step: 'waiting' }
  | { step: 'checking' }
  | { step: 'paid' }
  | { step: 'failed'; reason: string };

interface PaymentScreenProps {
  order: Order;
  method: 'mtn_momo' | 'orange_money';
  phone: string; // 9 digits, already cleaned
  delayMs?: number;
}

const PROVIDER = { mtn_momo: 'MTN Mobile Money', orange_money: 'Orange Money' };

// A SIMULATED payment: no real request is sent and no money moves.
// TODO 7: a SIMULATED mobile-money payment screen. Keep a Step in state, starting
// as { step: 'ready' }, and show ONE thing for each step (inside <div role="status">):
//   ready    "We will send a payment request to 670 00 00 01." + a button
//            "Send payment request to 670 00 00 01" -> step 'waiting'
//   waiting  "Request sent to ... Check your phone and approve the payment with your
//            <provider> PIN. Never type your PIN on a website: we will never ask for it."
//            + a box (className "practice") titled "Practice only: pretend to be the
//            customer's phone" with three buttons: "Customer approves",
//            "Customer declines", "No answer (time out)"
//   checking "Checking the payment status..."  (while await checkStatus(answer, delayMs) runs)
//   paid     "Payment received (simulated). Order <id> is paid. ..."
//   failed   "Payment not completed: <reason>" + a "Try again" button -> step 'ready'
// The heading stays: "Pay <total> with <provider>". NEVER add a PIN field.
export default function PaymentScreen({ order, method, phone, delayMs = 800 }: PaymentScreenProps) {
  console.log('TODO 7: the payment steps for', phone, delayMs);
  return (
    <section className="panel" aria-labelledby="pay-title">
      <h2 id="pay-title">Pay {formatFcfa(order.total_fcfa)} with {PROVIDER[method]}</h2>
      <p>TODO 7: the payment steps</p>
    </section>
  );
}
