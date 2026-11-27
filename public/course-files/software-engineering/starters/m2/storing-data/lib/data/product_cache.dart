// The last product list we downloaded, kept on the phone.
import 'dart:convert';

import 'package:sqflite/sqflite.dart';

import '../models/product.dart';

class ProductCache {
  ProductCache(this.db);

  final Database db;

  static const _savedAtKey = 'products_saved_at';

  // Replace the whole cached list in ONE transaction: if the phone dies half-way,
  // the old list stays. Also remember when we saved it.
  Future<void> saveAll(List<Product> products, {DateTime? now}) async {
    // TODO 1: inside db.transaction((txn) async { ... }):
    //   - delete every row of 'products'           (txn.delete)
    //   - insert each product with productToRow()  (txn.insert)
    //   - insert {'key': _savedAtKey, 'value': (now ?? DateTime.now()).toIso8601String()} into 'meta',
    //     with conflictAlgorithm: ConflictAlgorithm.replace (so a second save replaces the date).
    //   Use txn, not db, inside the transaction.
  }

  Future<List<Product>> loadAll() async {
    // TODO 2: query 'products' ordered by id, and turn each row into a Product (productFromRow).
    return [];
  }

  // When the list was saved, or null if it never was.
  Future<DateTime?> savedAt() async {
    // TODO 3: query 'meta' with where: 'key = ?' and whereArgs: [_savedAtKey].
    //         No row: return null. Otherwise DateTime.parse the 'value'.
    return null;
  }
}

// A product as a database row. The stock map does not fit in one column, so it is stored as JSON text.
// TODO 4: one key per column of the products table (see local_db.dart).
//         stock_json is jsonEncode(p.stock).
Map<String, Object?> productToRow(Product p) => {'id': p.id};

Product productFromRow(Map<String, Object?> row) => Product(
      id: row['id'] as int,
      sku: row['sku'] as String,
      name: row['name'] as String,
      category: row['category'] as String,
      unit: row['unit'] as String,
      priceFcfa: row['price_fcfa'] as int,
      stock: Map<String, int>.from(jsonDecode(row['stock_json'] as String) as Map),
      reorderLevel: row['reorder_level'] as int,
    );
