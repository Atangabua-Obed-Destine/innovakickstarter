/// One product as the rep's app sees it at one branch.
class Product {
  const Product({
    required this.id,
    required this.name,
    required this.priceFcfa,
    required this.stock,
  });

  final int id;
  final String name;
  final int priceFcfa; // whole FCFA, VAT included
  final int stock; // how many the branch has right now
}

/// One line of the basket: a product and how many.
class BasketLine {
  BasketLine(this.product, this.quantity);

  final Product product;
  int quantity;

  int get lineTotalFcfa => product.priceFcfa * quantity;
}

/// The rep's basket for one customer.
/// The server checks prices and stock again when the order is sent;
/// the basket only helps the rep avoid mistakes before that.
class Basket {
  final List<BasketLine> _lines = [];

  List<BasketLine> get lines => List.unmodifiable(_lines);

  bool get isEmpty => _lines.isEmpty;

  /// Number of items, counting quantities (2 bags of rice = 2).
  int get itemCount => _lines.fold(0, (sum, line) => sum + line.quantity);

  int get totalFcfa => _lines.fold(0, (sum, line) => sum + line.lineTotalFcfa);

  /// Adds [quantity] of [product].
  /// Returns false, and changes nothing, if the branch does not have enough.
  bool add(Product product, {int quantity = 1}) {
    if (quantity < 1) {
      throw ArgumentError.value(quantity, 'quantity', 'must be at least 1');
    }
    if (quantity > product.stock) return false;
    _lines.add(BasketLine(product, quantity));
    return true;
  }

  /// Changes the quantity of a product already in the basket. 0 removes it.
  void setQuantity(int productId, int quantity) {
    if (quantity < 0) {
      throw ArgumentError.value(quantity, 'quantity', 'cannot be negative');
    }
    if (quantity == 0) {
      _lines.removeWhere((line) => line.product.id == productId);
      return;
    }
    for (final line in _lines) {
      if (line.product.id == productId) line.quantity = quantity;
    }
  }

  void clear() => _lines.clear();
}
