// The database file on the phone. SQLite is a whole SQL database in one file.
// This file is ready: read the CREATE TABLE statements, they are the plan for your data.
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

class LocalDb {
  static const fileName = 'nkwen_rep.db';

  // Raise this number when you change the tables, and add an onUpgrade step:
  // phones that already have version 1 keep their file and their orders.
  static const version = 1;

  // Tests pass their own factory (sqflite_common_ffi) and an in-memory path.
  static Future<Database> open({DatabaseFactory? factory, String? path}) async {
    final f = factory ?? databaseFactory;
    final dbPath = path ?? p.join(await f.getDatabasesPath(), fileName);
    return f.openDatabase(
      dbPath,
      options: OpenDatabaseOptions(
        version: version,
        onConfigure: (db) => db.execute('PRAGMA foreign_keys = ON'),
        onCreate: _create,
      ),
    );
  }

  static Future<void> _create(Database db, int version) async {
    // The last product list we downloaded, so the rep can browse with no network.
    await db.execute('''
      CREATE TABLE products (
        id INTEGER PRIMARY KEY,
        sku TEXT NOT NULL,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        unit TEXT NOT NULL,
        price_fcfa INTEGER NOT NULL,
        stock_json TEXT NOT NULL,
        reorder_level INTEGER NOT NULL
      )''');
    // Orders saved on the phone. local_id is a UUID made on the phone.
    // sync_status is the phone's job (pending, synced, needs_attention);
    // server_status is the order's status on the server (awaiting_payment, paid...).
    await db.execute('''
      CREATE TABLE orders (
        local_id TEXT PRIMARY KEY,
        branch TEXT NOT NULL,
        customer_name TEXT NOT NULL,
        customer_phone TEXT,
        payment_method TEXT NOT NULL,
        estimated_total_fcfa INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        sync_status TEXT NOT NULL DEFAULT 'pending',
        attempts INTEGER NOT NULL DEFAULT 0,
        last_error TEXT,
        server_id TEXT,
        server_total_fcfa INTEGER,
        server_status TEXT,
        synced_at TEXT
      )''');
    // One row per line of an order.
    await db.execute('''
      CREATE TABLE order_items (
        local_id TEXT NOT NULL REFERENCES orders(local_id) ON DELETE CASCADE,
        product_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        unit_price_fcfa INTEGER NOT NULL,
        PRIMARY KEY (local_id, product_id)
      )''');
    // Small settings, such as when the products were saved.
    await db.execute('CREATE TABLE meta (key TEXT PRIMARY KEY, value TEXT NOT NULL)');
  }
}
