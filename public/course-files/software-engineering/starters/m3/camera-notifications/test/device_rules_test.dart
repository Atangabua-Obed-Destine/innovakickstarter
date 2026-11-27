// Self-check tests: the rules behind receipt photos, reminders and permissions.
// They need no phone. Do not change them: make them pass.
// Run:  flutter test
import 'package:flutter_test/flutter_test.dart';
import 'package:nkwen_device/permissions/permission_coach.dart';
import 'package:nkwen_device/receipts/receipt_rules.dart';
import 'package:nkwen_device/reminders/delivery_reminders.dart';

PermissionFacts facts({bool granted = false, int denied = 0, bool notNow = false, bool tapped = true}) =>
    PermissionFacts(granted: granted, timesDenied: denied, saidNotNowToday: notNow, repTappedFeature: tapped);

void main() {
  group('reminderTimes', () {
    final monday9 = DateTime(2026, 12, 7, 9, 0); // "now" in most tests

    test('a delivery on Wednesday at 14:00 gets two reminders', () {
      final times = reminderTimes(DateTime(2026, 12, 9, 14, 0), monday9);
      expect(times, [DateTime(2026, 12, 8, 18, 0), DateTime(2026, 12, 9, 13, 0)]);
    });

    test('an early delivery moves the hour-before reminder out of quiet hours', () {
      final times = reminderTimes(DateTime(2026, 12, 9, 7, 30), monday9);
      expect(times.last, DateTime(2026, 12, 9, 7, 0));
    });

    test('reminders in the past are dropped', () {
      final now = DateTime(2026, 12, 8, 19, 0); // already after 18:00 the evening before
      expect(reminderTimes(DateTime(2026, 12, 9, 14, 0), now), [DateTime(2026, 12, 9, 13, 0)]);
    });

    test('a reminder is never at or after the delivery itself', () {
      final times = reminderTimes(DateTime(2026, 12, 9, 7, 0), monday9);
      for (final t in times) {
        expect(t.isBefore(DateTime(2026, 12, 9, 7, 0)), isTrue);
      }
      expect(times, [DateTime(2026, 12, 8, 18, 0)]);
    });

    test('the day before the 1st of a month is the last day of the previous month', () {
      final times = reminderTimes(DateTime(2027, 1, 1, 10, 0), monday9);
      expect(times.first, DateTime(2026, 12, 31, 18, 0));
    });

    test('no reminder ever falls in quiet hours (21:00 to 07:00)', () {
      for (var hour = 0; hour < 24; hour++) {
        for (final t in reminderTimes(DateTime(2026, 12, 9, hour, 30), monday9)) {
          expect(t.hour >= 7 && t.hour < 21, isTrue, reason: 'delivery at $hour:30 gave a reminder at $t');
        }
      }
    });
  });

  group('notification ids', () {
    test('the same order and reminder always get the same id', () {
      expect(notificationIdFor('ORD-7Q2K9X1B', 0), notificationIdFor('ORD-7Q2K9X1B', 0));
    });

    test('ids differ between the two reminders and between orders', () {
      final ids = {
        notificationIdFor('ORD-7Q2K9X1B', 0),
        notificationIdFor('ORD-7Q2K9X1B', 1),
        notificationIdFor('ORD-3M8D2K7A', 0),
        notificationIdFor('ORD-3M8D2K7A', 1),
      };
      expect(ids.length, 4);
    });

    test('ids are positive 32-bit numbers, as Android needs', () {
      for (final id in [notificationIdFor('ORD-7Q2K9X1B', 0), notificationIdFor('ORD-ZZZZZZZZ', 1)]) {
        expect(id >= 0 && id < 0x80000000, isTrue);
      }
    });

    test('delivered and cancelled orders need no reminders', () {
      expect(wantsReminders('paid'), isTrue);
      expect(wantsReminders('delivered'), isFalse);
      expect(wantsReminders('cancelled'), isFalse);
    });
  });

  group('receipt photos', () {
    test('the file name holds the order and the time', () {
      expect(receiptFileName('ORD-7Q2K9X1B', DateTime(2026, 12, 3, 10, 5)), 'receipt_ORD-7Q2K9X1B_20261203-1005.jpg');
    });

    test('empty and very big photos get a kind message; normal ones pass', () {
      expect(receiptProblem(0), isNotNull);
      expect(receiptProblem(180 * 1024), isNull);
      expect(receiptProblem(receiptMaxBytes), isNull);
      expect(receiptProblem(receiptMaxBytes + 1), contains('KB'));
    });
  });

  group('asking for permission kindly', () {
    test('granted: just use the feature', () {
      expect(nextStep(facts(granted: true)), NextStep.useFeature);
    });

    test('never ask out of the blue: only when the rep taps the feature', () {
      expect(nextStep(facts(tapped: false)), NextStep.featureOff);
    });

    test('the first time, explain before Android asks', () {
      expect(nextStep(facts()), NextStep.explainFirst);
      expect(afterExplanation(repChoseAllow: true), NextStep.askSystem);
      expect(afterExplanation(repChoseAllow: false), NextStep.featureOff);
    });

    test('"Not now" is respected for the rest of the day: no nagging', () {
      expect(nextStep(facts(notNow: true)), NextStep.featureOff);
    });

    test('after two Denies, Android will not ask again: explain how to turn it on', () {
      expect(nextStep(facts(denied: 1)), NextStep.explainFirst);
      expect(nextStep(facts(denied: 2)), NextStep.howToTurnOn);
    });

    test('the explanations are specific and short', () {
      for (final f in Feature.values) {
        expect(whyWeAsk(f).split(' ').length, lessThan(45));
        expect(whenOff(f), isNotEmpty);
      }
      expect(whyWeAsk(Feature.camera), contains('delivery note'));
    });
  });
}
