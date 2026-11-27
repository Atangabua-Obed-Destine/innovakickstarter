import 'dart:io';

import 'package:material_ui/material_ui.dart';

import 'device/phone_features.dart';
import 'permissions/permission_coach.dart';
import 'receipts/receipt_rules.dart';

/// A small demo to try on your phone: a receipt photo and a delivery reminder.
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final reminders = DeliveryReminders();
  await reminders.init();
  runApp(MaterialApp(
    title: 'Nkwen Rep: device',
    theme: ThemeData(colorSchemeSeed: Colors.green),
    home: DeviceDemo(reminders: reminders),
  ));
}

class DeviceDemo extends StatefulWidget {
  const DeviceDemo({super.key, required this.reminders});
  final DeliveryReminders reminders;
  @override
  State<DeviceDemo> createState() => _DeviceDemoState();
}

class _DeviceDemoState extends State<DeviceDemo> {
  String? photoPath;
  String note = '';
  int timesDenied = 0;
  bool saidNotNow = false;

  @override
  void initState() {
    super.initState();
    recoverLostReceiptPhoto().then((file) {
      if (file != null && mounted) setState(() => photoPath = file.path);
    });
  }

  Future<void> _addPhoto() async {
    final file = await takeReceiptPhoto();
    if (file == null) return; // the rep cancelled: that is fine
    final problem = receiptProblem(await File(file.path).length());
    setState(() {
      note = problem ?? 'Saved as ${receiptFileName('ORD-7Q2K9X1B', DateTime.now())}';
      if (problem == null) photoPath = file.path;
    });
  }

  Future<void> _turnOnReminders() async {
    final granted = await widget.reminders.enabled();
    final step = nextStep(PermissionFacts(
        granted: granted, timesDenied: timesDenied, saidNotNowToday: saidNotNow, repTappedFeature: true));
    if (!mounted) return;
    switch (step) {
      case NextStep.useFeature:
        await _schedule();
      case NextStep.explainFirst:
        final allow = await showModalBottomSheet<bool>(
          context: context,
          builder: (context) => Padding(
            padding: const EdgeInsets.all(24),
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              Text(whyWeAsk(Feature.notifications)),
              const SizedBox(height: 16),
              Row(mainAxisAlignment: MainAxisAlignment.end, children: [
                TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Not now')),
                FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Allow')),
              ]),
            ]),
          ),
        );
        if (afterExplanation(repChoseAllow: allow ?? false) == NextStep.askSystem) {
          if (await widget.reminders.askAndroid()) {
            await _schedule();
          } else {
            timesDenied++;
            setState(() => note = whenOff(Feature.notifications));
          }
        } else {
          saidNotNow = true;
          setState(() => note = whenOff(Feature.notifications));
        }
      case NextStep.howToTurnOn:
        setState(() => note = 'Reminders are off. To turn them on: Settings > Apps > Nkwen Rep > Notifications.');
      case NextStep.featureOff:
      case NextStep.askSystem:
        setState(() => note = whenOff(Feature.notifications));
    }
  }

  Future<void> _schedule() async {
    final delivery = DateTime.now().add(const Duration(days: 1, hours: 2));
    await widget.reminders.scheduleFor(orderId: 'ORD-7Q2K9X1B', customer: 'Mbah Clarisse', delivery: delivery);
    setState(() => note = 'Reminders set for the delivery on $delivery.');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Order ORD-7Q2K9X1B')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (photoPath != null) Image.file(File(photoPath!), height: 200, cacheHeight: 400),
        FilledButton.icon(onPressed: _addPhoto, icon: const Icon(Icons.camera_alt), label: const Text('Add receipt photo')),
        const SizedBox(height: 12),
        OutlinedButton.icon(
            onPressed: _turnOnReminders, icon: const Icon(Icons.alarm), label: const Text('Remind me about this delivery')),
        const SizedBox(height: 16),
        Text(note),
      ]),
    );
  }
}
