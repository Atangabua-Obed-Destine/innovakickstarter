// Self-check for "Setting Up Flutter & Your First App".
// Run it from the project folder:  flutter test
// Do not change this file. If a test fails, fix lib/screens/visits_screen.dart.
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:nkwen_rep_app/screens/visits_screen.dart';

Future<void> showVisits(WidgetTester tester) async {
  await tester.pumpWidget(const MaterialApp(home: VisitsScreen()));
}

void main() {
  testWidgets('the top bar says Nkwen Rep', (tester) async {
    await showVisits(tester);
    expect(find.text('Nkwen Rep'), findsOneWidget, reason: 'TODO 3: change the AppBar title.');
  });

  testWidgets('the count starts at 0', (tester) async {
    await showVisits(tester);
    expect(find.text('Shops visited today'), findsOneWidget);
    expect(find.text('0'), findsOneWidget);
  });

  testWidgets('tapping "Add visit" adds 1', (tester) async {
    await showVisits(tester);
    expect(find.byTooltip('Add visit'), findsOneWidget, reason: 'TODO 5: the button tooltip must be "Add visit".');
    await tester.tap(find.byTooltip('Add visit'));
    await tester.pump(); // draw the next frame
    expect(find.text('1'), findsOneWidget, reason: 'TODO 1: change _visits inside setState().');
  });

  testWidgets('the target message appears only after 8 visits', (tester) async {
    await showVisits(tester);
    for (var i = 0; i < 7; i++) {
      await tester.tap(find.byTooltip('Add visit'));
      await tester.pump();
    }
    expect(find.textContaining('Target reached'), findsNothing, reason: '7 visits is not the target yet.');
    await tester.tap(find.byTooltip('Add visit'));
    await tester.pump();
    expect(find.text('Target reached. Well done!'), findsOneWidget, reason: 'TODO 4: show the message at 8 visits or more.');
  });

  testWidgets('Reset sets the count back to 0', (tester) async {
    await showVisits(tester);
    await tester.tap(find.byTooltip('Add visit'));
    await tester.tap(find.byTooltip('Add visit'));
    await tester.pump();
    await tester.tap(find.byTooltip('Reset'));
    await tester.pump();
    expect(find.text('0'), findsOneWidget, reason: 'TODO 2: _reset() must set _visits to 0 inside setState().');
  });
}
