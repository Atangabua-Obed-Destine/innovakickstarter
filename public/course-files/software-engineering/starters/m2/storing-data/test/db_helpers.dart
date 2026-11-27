// Database helpers for the self-check tests. Do not change this file.
// On the phone, sqflite uses Android's SQLite. In tests on your laptop,
// sqflite_common_ffi runs the same SQL with a desktop SQLite, in memory.
import 'package:nkwen_rep_app/data/local_db.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

// A fresh, empty database with the app's tables. [noIsolate] is needed inside testWidgets.
Future<Database> openTestDb({bool noIsolate = false}) {
  sqfliteFfiInit();
  return LocalDb.open(
    factory: noIsolate ? databaseFactoryFfiNoIsolate : databaseFactoryFfi,
    path: inMemoryDatabasePath,
  );
}
