import 'package:flutter/foundation.dart';

import '../models/customer.dart';
import '../models/product.dart';

// One line of the order: a product and how many.
class OrderLine {
  const OrderLine({required this.product, required this.quantity});

  final Product product;
  final int quantity;

  // An estimate: the server sets the real price when the order is sent.
  int get lineTotalFcfa => product.priceFcfa * quantity;
}

// The order the rep is building. Several screens read and change it, so it is
// "app state": it lives above the screens and tells them when it changes.
//
// RULE: every method that changes something must end with notifyListeners().
// That is how the screens that "watch" this object know they must redraw.
class OrderDraft extends ChangeNotifier {
  // The Orders API refuses more than 100 of one product in one order.
  static const maxQuantity = 100;

  final List<OrderLine> _lines = [];
  Customer? _customer;
  String _branch = 'NKW';
  String _paymentMethod = 'cash';

  // TODO 1: screens may read the lines but must not change the list directly.
  //         Return List.unmodifiable(_lines) instead.
  List<OrderLine> get lines => _lines;
  Customer? get customer => _customer;
  String get branch => _branch;
  String get paymentMethod => _paymentMethod;

  bool get isEmpty => _lines.isEmpty;

  // TODO 2: the number of items: the sum of every line's quantity.
  //         Tip: _lines.fold(0, (sum, line) => sum + line.quantity)
  int get itemCount => 0;

  // TODO 3: the estimated total: the sum of every line's lineTotalFcfa.
  int get totalFcfa => 0;

  int quantityOf(int productId) {
    final index = _indexOf(productId);
    return index == -1 ? 0 : _lines[index].quantity;
  }

  // TODO 4: add a product.
  //  - do nothing if quantity is 0 or less;
  //  - if the product already has a line, replace that line with one whose quantity is
  //    old + new (OrderLine is immutable: make a new one);
  //  - otherwise add a new line;
  //  - never go above maxQuantity (use _cap);
  //  - then notifyListeners().
  void add(Product product, int quantity) {}

  // TODO 5: set the quantity of a line. 0 or less removes the line. Cap at maxQuantity.
  //         An unknown productId does nothing. Then notifyListeners().
  void setQuantity(int productId, int quantity) {}

  // TODO 6: remove the line of this product, then notifyListeners().
  void remove(int productId) {}

  void setCustomer(Customer? customer) {
    _customer = customer;
    notifyListeners();
  }

  // TODO 7: like setCustomer, write setBranch and setPaymentMethod.
  void setBranch(String code) {}

  void setPaymentMethod(String method) {}

  // TODO 8: start a new order: remove every line and the customer.
  //         Keep the branch and the payment method. Then notifyListeners().
  void clear() {}

  int _indexOf(int productId) => _lines.indexWhere((line) => line.product.id == productId);

  int _cap(int quantity) => quantity > maxQuantity ? maxQuantity : quantity;
}
