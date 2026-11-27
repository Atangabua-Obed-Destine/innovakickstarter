// One product, as the Nkwen Traders API describes it.
class Product {
  const Product({
    required this.id,
    required this.sku,
    required this.name,
    required this.category,
    required this.unit,
    required this.priceFcfa,
    required this.stock,
    required this.reorderLevel,
  });

  final int id;
  final String sku;
  final String name;
  final String category;
  final String unit;
  final int priceFcfa; // whole FCFA, VAT included
  final Map<String, int> stock; // branch code -> quantity
  final int reorderLevel;

  // TODO 4: copy your Product.fromJson from dart_basics.dart and add the new field:
  //   reorderLevel comes from json['reorder_level'] (use 0 if it is missing).
  factory Product.fromJson(Map<String, dynamic> json) {
    throw UnimplementedError('TODO 4: Product.fromJson');
  }

  // TODO 5: copy your stockAt(). A branch that is not in the map (LMB) has 0.
  int stockAt(String branch) => -1;
}
