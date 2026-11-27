/// Asking for permissions kindly, following Android's own advice:
/// ask in context, explain first, never block the app, accept "no".
library;

enum Feature { camera, notifications }

/// What the app should do next when the rep taps a feature that needs a permission.
enum NextStep {
  useFeature, // permission granted: just do it
  explainFirst, // our own short screen: why we need it, with "Allow" and "Not now"
  askSystem, // show Android's permission dialog
  featureOff, // respect the "no": keep the app working without this feature
  howToTurnOn, // the rep asked again themselves: say how to turn it on in Settings
}

/// What the app knows about one permission.
class PermissionFacts {
  const PermissionFacts({
    required this.granted,
    required this.timesDenied,
    required this.saidNotNowToday,
    required this.repTappedFeature,
  });

  final bool granted;

  /// How many times the rep pressed Deny in Android's dialog (Android stops
  /// showing the dialog after the second Deny).
  final int timesDenied;

  /// The rep pressed "Not now" on our own explanation screen today.
  final bool saidNotNowToday;

  /// The rep just tapped the feature themselves (for example "Add receipt photo").
  final bool repTappedFeature;
}

NextStep nextStep(PermissionFacts f) {
  // TODO 4: decide, in this order:
  //   granted -> useFeature
  //   the rep did not tap the feature -> featureOff (never ask out of the blue)
  //   denied twice or more -> howToTurnOn (Android will not show its dialog again)
  //   said "Not now" today -> featureOff (no nagging)
  //   otherwise -> explainFirst
  throw UnimplementedError('TODO: write nextStep() (step 4)');
}

/// After our explanation screen: "Allow" shows Android's dialog, "Not now" keeps the feature off.
NextStep afterExplanation({required bool repChoseAllow}) =>
    repChoseAllow ? NextStep.askSystem : NextStep.featureOff;

/// The words on our explanation screen. Plain, specific, honest.
String whyWeAsk(Feature feature) {
  switch (feature) {
    case Feature.camera:
      return 'Take a photo of the signed delivery note, so head office can see the goods arrived. '
          'The camera opens only when you tap "Add receipt photo". You can skip the photo.';
    case Feature.notifications:
      return 'We remind you at 18:00 the evening before a delivery, and one hour before it. '
          'No other messages. You can turn reminders off at any time.';
  }
}

/// What the app says where the feature would be, when it is off.
String whenOff(Feature feature) {
  switch (feature) {
    case Feature.camera:
      return 'Receipt photos are off. You can still save the order without a photo.';
    case Feature.notifications:
      return 'Delivery reminders are off. Check "Today\'s deliveries" each morning.';
  }
}
