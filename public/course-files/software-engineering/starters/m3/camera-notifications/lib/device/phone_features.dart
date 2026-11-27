// The REAL phone features: camera and notifications, through plugins.
// Tests do not import this file (plugins need a phone). Try it on your phone.
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_timezone/flutter_timezone.dart';
import 'package:image_picker/image_picker.dart';
import 'package:timezone/data/latest_all.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

import '../receipts/receipt_rules.dart';
import '../reminders/delivery_reminders.dart';

/// Opens the camera. Returns null if the rep cancels.
/// image_picker makes the photo smaller for us (maxWidth, imageQuality).
Future<XFile?> takeReceiptPhoto() {
  return ImagePicker().pickImage(
    source: ImageSource.camera,
    maxWidth: receiptMaxWidth,
    imageQuality: receiptQuality,
  );
}

/// On a cheap phone, Android may close our app while the camera is open.
/// Call this when the app starts, to get back a photo taken just before.
Future<XFile?> recoverLostReceiptPhoto() async {
  final response = await ImagePicker().retrieveLostData();
  if (response.isEmpty) return null;
  return response.file;
}

class DeliveryReminders {
  final _plugin = FlutterLocalNotificationsPlugin();

  AndroidFlutterLocalNotificationsPlugin? get _android =>
      _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

  /// Call once at start-up. It does NOT ask for permission.
  Future<void> init() async {
    tz.initializeTimeZones();
    try {
      final zone = await FlutterTimezone.getLocalTimezone();
      tz.setLocalLocation(tz.getLocation(zone.identifier));
    } catch (_) {
      tz.setLocalLocation(tz.getLocation('Africa/Douala')); // Cameroon
    }
    await _plugin.initialize(
      settings: const InitializationSettings(android: AndroidInitializationSettings('@mipmap/ic_launcher')),
    );
  }

  Future<bool> enabled() async => await _android?.areNotificationsEnabled() ?? false;

  /// Shows Android's dialog (Android 13 and newer). Call it only after
  /// the rep pressed "Allow" on our own explanation screen.
  Future<bool> askAndroid() async => await _android?.requestNotificationsPermission() ?? false;

  /// Schedules (or replaces) the reminders for one order.
  Future<void> scheduleFor({required String orderId, required String customer, required DateTime delivery}) async {
    const details = NotificationDetails(
      android: AndroidNotificationDetails('deliveries', 'Delivery reminders',
          channelDescription: 'Reminders before a delivery to a customer'),
    );
    final times = reminderTimes(delivery, DateTime.now());
    for (var which = 0; which < 2; which++) {
      await _plugin.cancel(id: notificationIdFor(orderId, which));
    }
    for (final time in times) {
      final sameDay = time.year == delivery.year && time.month == delivery.month && time.day == delivery.day;
      final which = sameDay ? 1 : 0; // 0 = the evening before, 1 = on the day
      await _plugin.zonedSchedule(
        id: notificationIdFor(orderId, which),
        title: which == 0 ? 'Delivery tomorrow' : 'Delivery today',
        body: '$orderId for $customer',
        scheduledDate: tz.TZDateTime.from(time, tz.local),
        notificationDetails: details,
        // Inexact: Android may show it a few minutes late, but we need no
        // special "exact alarm" permission, and the battery is spared.
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
      );
    }
  }

  /// When an order is delivered or cancelled, remove its reminders.
  Future<void> cancelFor(String orderId) async {
    for (var which = 0; which < 2; which++) {
      await _plugin.cancel(id: notificationIdFor(orderId, which));
    }
  }
}
