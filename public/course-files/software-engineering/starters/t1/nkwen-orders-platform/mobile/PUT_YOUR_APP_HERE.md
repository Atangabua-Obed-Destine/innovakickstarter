# mobile/: the sales-rep app (Flutter)

The mobile members put the Flutter project here, so that `mobile/pubspec.yaml` exists.
The CI job "Rep app (Flutter)" then runs `flutter analyze` and `flutter test` on every pull request.

- Read the API address with `String.fromEnvironment('API_URL')` and pass it with
  `flutter run --dart-define=API_URL=...`, never a hard-coded URL.
- Copy `contract/consumers/mobile/order_contract_test.dart` into `mobile/test/` (see that file).

Usually you start from one member's Offline Order Taking or released rep app (Mobile Chapters 8-9).
Copy its files here (not `.git`, not `build/`, not `.dart_tool/`), open a pull request, then delete this file.
