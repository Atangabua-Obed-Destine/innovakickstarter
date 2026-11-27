// A SIMULATED mobile-money payment (ready for you). Nothing here talks to MTN or
// Orange, and no money moves. It copies the shape of a real request-to-pay:
// the request is accepted at once, the status is PENDING while the customer
// decides ON THEIR OWN PHONE, and the shop then checks the final status.
// In the real system our server makes these calls (Backend pathway, Chapter 9),
// never the browser: the provider's keys must stay secret.

export type MomoStatus = 'PENDING' | 'SUCCESSFUL' | 'FAILED';
export type PhoneAnswer = 'approve' | 'decline' | 'timeout';

export interface StatusCheck {
  status: MomoStatus;
  reason?: string;
}

// What the status check would say after the customer's phone answered.
export function checkStatus(answer: PhoneAnswer, delayMs = 800): Promise<StatusCheck> {
  const result: StatusCheck =
    answer === 'approve'
      ? { status: 'SUCCESSFUL' }
      : answer === 'decline'
        ? { status: 'FAILED', reason: 'The customer declined the payment on their phone.' }
        : { status: 'FAILED', reason: 'No answer from the phone in time (MTN allows about 5 minutes).' };
  return new Promise((resolve) => setTimeout(() => resolve(result), delayMs));
}

// "670000001" -> "670 00 00 01"
export function formatPhone(digits: string): string {
  return digits.replace(/^(\d{3})(\d{2})(\d{2})(\d{2})$/, '$1 $2 $3 $4');
}
