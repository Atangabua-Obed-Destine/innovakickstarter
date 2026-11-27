import 'package:flutter/foundation.dart';

// The quick-order cart: it only counts items. This file has no bug.
class Mb1Cart extends ChangeNotifier {
  int _count = 0;

  int get count => _count;

  void add(int quantity) {
    _count += quantity;
    notifyListeners();
  }
}
