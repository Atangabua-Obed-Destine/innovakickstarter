/// When should the rep's phone remind them about a delivery?
///
/// Head office's rules:
/// 1. The evening before: 18:00 on the day before the delivery.
/// 2. One hour before the delivery.
/// 3. Quiet hours: no reminder from 21:00 to 07:00. A reminder that falls
///    in quiet hours moves to the next 07:00.
/// 4. Every reminder must be in the future AND before the delivery.
///    Otherwise it is dropped.
/// 5. Return the times in order, without repeats.
List<DateTime> reminderTimes(DateTime delivery, DateTime now) {
  // TODO 1: build the two candidate times (18:00 the day before, and one hour before).
  //         Tip: DateTime(delivery.year, delivery.month, delivery.day - 1, 18) works
  //         even on the 1st of a month: Dart rolls back to the previous month.
  // TODO 2: move each one out of quiet hours with outOfQuietHours().
  // TODO 3: keep only the times after [now] and before [delivery]; sort; no repeats.
  throw UnimplementedError('TODO: write reminderTimes() (steps 1 to 3)');
}

/// Moves a time in quiet hours (21:00 to 07:00) to the next 07:00.
DateTime outOfQuietHours(DateTime t) {
  if (t.hour >= 21) return DateTime(t.year, t.month, t.day + 1, 7);
  if (t.hour < 7) return DateTime(t.year, t.month, t.day, 7);
  return t;
}

/// Reminders only make sense for orders that will still be delivered.
bool wantsReminders(String orderStatus) =>
    orderStatus != 'delivered' && orderStatus != 'cancelled';

/// A notification id that is the SAME every time for the same order and reminder,
/// so the app can replace or cancel it later. [which] is 0 (evening) or 1 (hour before).
/// Dart's String.hashCode is not promised to stay the same between runs, so we
/// compute our own (FNV-1a), kept positive and under 2^31 for Android.
int notificationIdFor(String orderId, int which) {
  var hash = 0x811c9dc5;
  for (final unit in orderId.codeUnits) {
    hash = ((hash ^ unit) * 0x01000193) & 0xffffffff;
  }
  return ((hash & 0x3fffffff) << 1) | (which & 1);
}
