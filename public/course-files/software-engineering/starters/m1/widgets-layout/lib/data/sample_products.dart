// Eight real Nkwen Traders products, copied from the course API (GET /products).
// We use them while we learn widgets. From the chapter project on, the app
// downloads every product from the API instead.
import '../models/product.dart';

const sampleProducts = <Product>[
  Product(id: 1, sku: 'NKT-BAK-001', name: 'Bread (loaf)', category: 'Bakery', unit: 'loaf', priceFcfa: 600, stock: {'NKW': 37, 'ML4': 0, 'CAV': 53, 'BAF': 16}, reorderLevel: 10),
  Product(id: 2, sku: 'NKT-DAI-002', name: 'Milk Powder 400g', category: 'Dairy', unit: 'tin', priceFcfa: 2875, stock: {'NKW': 74, 'ML4': 37, 'CAV': 0, 'BAF': 53}, reorderLevel: 10),
  Product(id: 5, sku: 'NKT-GRA-005', name: 'Rice 25kg', category: 'Grains', unit: 'bag', priceFcfa: 19055, stock: {'NKW': 5, 'ML4': 58, 'CAV': 3, 'BAF': 74}, reorderLevel: 5),
  Product(id: 6, sku: 'NKT-GRA-006', name: 'Rice 50kg', category: 'Grains', unit: 'bag', priceFcfa: 36005, stock: {'NKW': 42, 'ML4': 3, 'CAV': 58, 'BAF': 21}, reorderLevel: 5),
  Product(id: 7, sku: 'NKT-GRO-007', name: 'Maggi Cubes (pack)', category: 'Groceries', unit: 'pack', priceFcfa: 510, stock: {'NKW': 3, 'ML4': 42, 'CAV': 5, 'BAF': 58}, reorderLevel: 15),
  Product(id: 10, sku: 'NKT-GRO-010', name: 'Tomato Paste (tin)', category: 'Groceries', unit: 'tin', priceFcfa: 355, stock: {'NKW': 10, 'ML4': 0, 'CAV': 26, 'BAF': 79}, reorderLevel: 12),
  Product(id: 15, sku: 'NKT-OIL-015', name: 'Palm Oil 5L', category: 'Oils', unit: 'jerrycan', priceFcfa: 6600, stock: {'NKW': 15, 'ML4': 68, 'CAV': 31, 'BAF': 84}, reorderLevel: 6),
  Product(id: 16, sku: 'NKT-OIL-016', name: 'Vegetable Oil 5L', category: 'Oils', unit: 'jerrycan', priceFcfa: 7210, stock: {'NKW': 52, 'ML4': 15, 'CAV': 68, 'BAF': 31}, reorderLevel: 6),
];
