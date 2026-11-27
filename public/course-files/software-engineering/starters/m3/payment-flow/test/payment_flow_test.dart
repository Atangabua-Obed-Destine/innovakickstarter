// Self-check tests for the payment flow. Do not change them: make them pass.
// Run:  flutter test test/payment_flow_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_payments/payments/payment_flow.dart';
import 'package:nkwen_payments/payments/sandbox_payment_api.dart';

const order = 'ORD-7Q2K9X1B';
const approves = '46733123456'; // sandbox: the customer approves
const rejects = '46733123451'; // sandbox: the customer says no
const neverAnswers = '46733123453'; // sandbox: stays pending

/// A flow that does not really wait, and remembers every step it showed.
({PaymentFlow flow, List<PayStep> steps}) makeFlow(SandboxPaymentApi api) {
  final flow = PaymentFlow(api: api, orderId: order, totalFcfa: 78610, wait: (_) async {});
  final steps = <PayStep>[];
  flow.addListener(() {
    if (steps.isEmpty || steps.last != flow.step) steps.add(flow.step);
  });
  return (flow: flow, steps: steps);
}

void main() {
  test('a new flow is ready and nothing is paid', () {
    final api = SandboxPaymentApi();
    final f = makeFlow(api);
    expect(f.flow.step, PayStep.ready);
    expect(api.orderStatus[order], 'awaiting_payment');
  });

  test('a real-looking number is refused in sandbox mode, before any request', () async {
    final api = SandboxPaymentApi();
    final f = makeFlow(api);
    await f.flow.pay('677 12 34 56');
    expect(f.flow.step, PayStep.ready);
    expect(f.flow.message, isNotEmpty, reason: 'Tell the rep what is wrong.');
    expect(api.startCalls, 0, reason: 'Check the number BEFORE calling the server.');
  });

  test('202 is not "paid": the app waits for the server to confirm', () async {
    final api = SandboxPaymentApi(checksBeforeAnswer: 3);
    final f = makeFlow(api);
    await f.flow.pay(approves);
    expect(f.steps, [PayStep.sending, PayStep.waiting, PayStep.paid]);
    expect(api.checkCalls, 3, reason: 'Paid only after the server said so, on the 3rd check.');
    expect(api.orderStatus[order], 'paid');
    expect(f.flow.message, contains('78,610 FCFA'));
  });

  test('never shows paid while the payment is still pending', () async {
    final api = SandboxPaymentApi();
    final f = makeFlow(api);
    await f.flow.pay(neverAnswers);
    expect(f.steps, isNot(contains(PayStep.paid)));
    expect(f.flow.step, PayStep.unknown, reason: 'Pending for too long is unknown: not failed, not paid.');
    expect(f.flow.message, isNotEmpty);
    expect(api.checkCalls, PaymentFlow.defaultCheckDelays.length);
  });

  test('a rejected payment fails with words the rep can say', () async {
    final api = SandboxPaymentApi();
    final f = makeFlow(api);
    await f.flow.pay(rejects);
    expect(f.flow.step, PayStep.failed);
    expect(f.flow.message, friendlyReason('rejected'));
    expect(api.orderStatus[order], 'awaiting_payment');
  });

  test('a lost answer is retried with the SAME key: the customer is asked once', () async {
    final api = SandboxPaymentApi()..loseNextStartAnswers = 1;
    final f = makeFlow(api);
    await f.flow.pay(approves);
    expect(f.flow.step, PayStep.paid);
    expect(api.startCalls, 2);
    expect(api.keysSeen.toSet().length, 1, reason: 'Both requests must carry the same Idempotency-Key.');
    expect(api.paymentsCreated, 1);
  });

  test('no network at all: not failed, and paying again later is safe', () async {
    final api = SandboxPaymentApi()..loseNextStartAnswers = 3;
    final f = makeFlow(api);
    await f.flow.pay(approves);
    expect(f.flow.step, PayStep.unknown, reason: 'The server may have the request: we do not know.');

    await f.flow.pay(approves); // the network is back
    expect(f.flow.step, PayStep.paid);
    expect(api.keysSeen.toSet().length, 1, reason: 'Re-use the key after an unknown result.');
    expect(api.paymentsCreated, 1, reason: 'The customer must be asked only once.');
  });

  test('a dropped status check is not a failed payment', () async {
    final api = SandboxPaymentApi()..dropNextChecks = 2;
    final f = makeFlow(api);
    await f.flow.pay(approves);
    expect(f.steps, isNot(contains(PayStep.failed)));
    expect(f.flow.step, PayStep.paid);
  });

  test('a double tap starts only one payment', () async {
    final api = SandboxPaymentApi();
    final f = makeFlow(api);
    await Future.wait([f.flow.pay(approves), f.flow.pay(approves)]);
    expect(api.startCalls, 1);
    expect(f.flow.step, PayStep.paid);
  });

  test('after a definite failure, a new attempt uses a NEW key', () async {
    final api = SandboxPaymentApi();
    final f = makeFlow(api);
    await f.flow.pay(rejects);
    await f.flow.pay(approves);
    expect(f.flow.step, PayStep.paid);
    expect(api.keysSeen.toSet().length, 2);
    expect(api.paymentsCreated, 2);
  });

  test('an order that is already paid shows the server message (409)', () async {
    final api = SandboxPaymentApi()..orderStatus[order] = 'paid';
    final f = makeFlow(api);
    await f.flow.pay(approves);
    expect(f.flow.step, PayStep.failed);
    expect(f.flow.message, contains('already paid'));
  });

  test('the checks wait longer each time and stop within 5 minutes', () {
    final d = PaymentFlow.defaultCheckDelays;
    for (var i = 1; i < d.length; i++) {
      expect(d[i] > d[i - 1], isTrue);
    }
    final total = d.fold(Duration.zero, (a, b) => a + b);
    expect(total < const Duration(minutes: 5), isTrue);
  });
}
