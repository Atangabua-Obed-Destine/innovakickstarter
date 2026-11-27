// Dart for JavaScript Developers - Nkwen Traders practice file.
// Run it:  dart run dart_basics.dart
// No Dart on your laptop yet? Paste the whole file into https://dartpad.dev and press Run.
// Fill in every TODO. The self-check at the bottom tells you what is still wrong.

// ---------- The data (do not change) ----------
// This is what jsonDecode() gives you for part of GET /products:
// a List of Maps with String keys, like a JavaScript array of objects.
final List<Map<String, dynamic>> productsJson = [
  {'id': 1, 'sku': 'NKT-BAK-001', 'name': 'Bread (loaf)', 'category': 'Bakery', 'unit': 'loaf', 'price_fcfa': 600, 'stock': {'NKW': 37, 'ML4': 0, 'CAV': 53, 'BAF': 16}},
  {'id': 5, 'sku': 'NKT-GRA-005', 'name': 'Rice 25kg', 'category': 'Grains', 'unit': 'bag', 'price_fcfa': 19055, 'stock': {'NKW': 5, 'ML4': 58, 'CAV': 3, 'BAF': 74}},
  {'id': 6, 'sku': 'NKT-GRA-006', 'name': 'Rice 50kg', 'category': 'Grains', 'unit': 'bag', 'price_fcfa': 36005, 'stock': {'NKW': 42, 'ML4': 3, 'CAV': 58, 'BAF': 21}},
  {'id': 9, 'sku': 'NKT-GRO-009', 'name': 'Sugar 1kg', 'category': 'Groceries', 'unit': 'bag', 'price_fcfa': 760, 'stock': {'NKW': 63, 'ML4': 26, 'CAV': 0, 'BAF': 42}},
  {'id': 12, 'sku': 'NKT-HOU-012', 'name': 'Matches (box)', 'category': 'Household', 'unit': 'box', 'price_fcfa': 100, 'stock': {'NKW': 84, 'ML4': 47, 'CAV': 3, 'BAF': 63}},
  {'id': 13, 'sku': 'NKT-HOU-013', 'name': 'Soap (bar)', 'category': 'Household', 'unit': 'bar', 'price_fcfa': 410, 'stock': {'NKW': 31, 'ML4': 3, 'CAV': 47, 'BAF': 10}},
  {'id': 15, 'sku': 'NKT-OIL-015', 'name': 'Palm Oil 5L', 'category': 'Oils', 'unit': 'jerrycan', 'price_fcfa': 6600, 'stock': {'NKW': 15, 'ML4': 68, 'CAV': 31, 'BAF': 84}},
];

// A basket: product id -> quantity (a Map, like a JavaScript object used as a dictionary).
final Map<int, int> basket = {6: 1, 13: 4, 12: 10};

// ---------- TODO 1 ----------
// Return the amount with a comma for thousands and " FCFA" after it.
// Example of the format: 1500 gives "1,500 FCFA". Dart has no toLocaleString(),
// so build it yourself: turn the number into a String, then add a comma
// before every group of three digits, counting from the right.
String formatFcfa(int amount) {
  // TODO 1: write this function.
  return '';
}

// ---------- The Product class (TODO 2 and TODO 3 are inside it) ----------
class Product {
  final int id;
  final String sku;
  final String name;
  final String category;
  final String unit;
  final int priceFcfa;
  final Map<String, int> stock; // branch code -> quantity

  const Product({
    required this.id,
    required this.sku,
    required this.name,
    required this.category,
    required this.unit,
    required this.priceFcfa,
    required this.stock,
  });

  // ---------- TODO 2 ----------
  // Build a Product from one JSON map. JSON keys use snake_case (price_fcfa);
  // Dart fields use camelCase (priceFcfa). Use "as int" and "as String" to say the type.
  // Hint for stock: Map<String, int>.from(json['stock'] as Map)
  factory Product.fromJson(Map<String, dynamic> json) {
    // TODO 2: return a Product(...) built from json.
    throw UnimplementedError('TODO 2: Product.fromJson');
  }

  // ---------- TODO 3 ----------
  // Return the stock at one branch. A branch that is not in the map (for example
  // LMB, Limbe, which is not open yet) has 0. Hint: the ?? operator.
  int stockAt(String branch) {
    // TODO 3: write this method.
    return -1;
  }
}

// ---------- TODO 4 ----------
// Return a label such as "Bread (loaf) (600 FCFA)" with string interpolation.
// Use formatFcfa() for the price.
String productLabel(Product product) {
  // TODO 4: write this function.
  return '';
}

// ---------- TODO 5 ----------
// Return the product with this id, or null if there is none.
// The return type Product? means "a Product, or null".
Product? findProduct(List<Product> products, int id) {
  // TODO 5: write this function.
  return null;
}

// ---------- TODO 6 ----------
// Return only the products in this category, in any letter case ("household" works too).
// Hint: where(...) is Dart's filter(). It gives an Iterable, so finish with .toList().
List<Product> productsInCategory(List<Product> products, String category) {
  // TODO 6: write this function.
  return [];
}

// ---------- TODO 7 ----------
// Return the total price of the basket in FCFA (price x quantity for each line).
// Skip any id that is not in products. Hint: for (final entry in basket.entries).
int basketTotal(List<Product> products, Map<int, int> basket) {
  // TODO 7: write this function.
  return 0;
}

// ---------- TODO 8 ----------
// Pretend to download the products: wait 300 milliseconds, then return them as Products.
// A Future is Dart's Promise. Mark the function async and use await, as in JavaScript.
// Hint: await Future.delayed(const Duration(milliseconds: 300));
Future<List<Product>> loadProducts() async {
  // TODO 8: wait, then turn every map in productsJson into a Product.
  return [];
}

// ---------- Try it ----------
Future<void> main() async {
  print('Loading products...');
  final products = await loadProducts();
  print('Loaded ${products.length} products.');
  if (products.isNotEmpty) {
    print(productLabel(products.first));
  }
  print('Basket total: ${formatFcfa(basketTotal(products, basket))}');
  final rice = findProduct(products, 6);
  print('Rice 50kg at Limbe: ${rice?.stockAt('LMB')}');
  await selfCheck();
}

// ======================= SELF-CHECK (do not edit below) =======================
Future<void> selfCheck() async {
  final problems = <String>[];
  void check(String message, bool Function() test) {
    try {
      if (!test()) problems.add(message);
    } catch (e) {
      problems.add('$message (error: ${e.runtimeType})');
    }
  }

  check('formatFcfa(1500) should give "1,500 FCFA".', () => formatFcfa(1500) == '1,500 FCFA');
  check('formatFcfa(100) should give "100 FCFA".', () => formatFcfa(100) == '100 FCFA');
  check('formatFcfa(1234567) should give "1,234,567 FCFA".', () => formatFcfa(1234567) == '1,234,567 FCFA');
  final bread = <String, dynamic>{'id': 1, 'sku': 'NKT-BAK-001', 'name': 'Bread (loaf)', 'category': 'Bakery', 'unit': 'loaf', 'price_fcfa': 600, 'stock': {'NKW': 37, 'ML4': 0}};
  check('Product.fromJson() should copy every field (price_fcfa goes into priceFcfa).', () {
    final p = Product.fromJson(bread);
    return p.id == 1 && p.name == 'Bread (loaf)' && p.priceFcfa == 600 && p.unit == 'loaf' && p.stock['NKW'] == 37;
  });
  check('stockAt() should return the stock for a branch in the map.', () => Product.fromJson(bread).stockAt('NKW') == 37);
  check('stockAt() should return 0 for a branch that is not in the map (LMB).', () => Product.fromJson(bread).stockAt('LMB') == 0);
  check('productLabel() should give "Bread (loaf) (600 FCFA)" for bread.', () => productLabel(Product.fromJson(bread)) == 'Bread (loaf) (600 FCFA)');
  List<Product> all() => productsJson.map(Product.fromJson).toList();
  check('findProduct() should return null for an id that does not exist.', () => findProduct(all(), 999) == null);
  check('findProduct() should return the product for an id that exists.', () => findProduct(all(), all()[2].id)?.name == all()[2].name);
  check('productsInCategory() should ignore letter case.', () {
    final a = productsInCategory(all(), 'HOUSEHOLD').length;
    return a > 0 && a == productsInCategory(all(), 'Household').length;
  });
  check('Every product should be in exactly one category list.', () {
    final cats = all().map((p) => p.category).toSet();
    return cats.fold<int>(0, (sum, c) => sum + productsInCategory(all(), c).length) == all().length;
  });
  check('An unknown category should give an empty list.', () => productsInCategory(all(), 'Electronics').isEmpty);
  check('basketTotal() should add price x quantity for every line.', () {
    var byHand = 0;
    basket.forEach((id, qty) {
      for (final p in productsJson) {
        if (p['id'] == id) byHand += (p['price_fcfa'] as int) * qty;
      }
    });
    return byHand > 0 && basketTotal(all(), basket) == byHand;
  });
  check('basketTotal() should skip ids that are not in the products.', () => basketTotal(all(), {999: 3}) == 0);
  try {
    final watch = Stopwatch()..start();
    final loaded = await loadProducts();
    if (loaded.length != productsJson.length) problems.add('loadProducts() should return every product as a Product.');
    if (watch.elapsedMilliseconds < 250) problems.add('loadProducts() should wait about 300 milliseconds (use await Future.delayed).');
  } catch (e) {
    problems.add('loadProducts() should return every product as a Product. (error: ${e.runtimeType})');
  }

  print('');
  if (problems.isEmpty) {
    print('Self-check passed: every TODO works.');
  } else {
    print('Self-check: ${problems.length} problem(s) to fix:');
    for (final p in problems) {
      print(' - $p');
    }
  }
}
