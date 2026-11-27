// Data for the Quick Order screen (Problem Solving MB1). Do not change this file.
class Mb1Product {
  const Mb1Product(this.id, this.name, this.priceFcfa, this.stock);

  final int id;
  final String name;
  final int priceFcfa;
  final Map<String, int> stock; // only OPEN branches are in this map
}

const mb1Products = <Mb1Product>[
  Mb1Product(16, 'Vegetable Oil 5L', 7210, {'NKW': 52, 'ML4': 15, 'CAV': 68, 'BAF': 31}),
  Mb1Product(2, 'Milk Powder 400g', 2875, {'NKW': 74, 'ML4': 37, 'CAV': 0, 'BAF': 53}),
  Mb1Product(10, 'Tomato Paste (tin)', 355, {'NKW': 10, 'ML4': 0, 'CAV': 26, 'BAF': 79}),
  Mb1Product(6, 'Rice 50kg', 36005, {'NKW': 42, 'ML4': 3, 'CAV': 58, 'BAF': 21}),
  Mb1Product(7, 'Maggi Cubes (pack)', 510, {'NKW': 3, 'ML4': 42, 'CAV': 5, 'BAF': 58}),
];

// Every branch, including Limbe, which opens in 2027.
const mb1Branches = {
  'NKW': 'Nkwen',
  'ML4': 'Mile 4',
  'CAV': 'Commercial Avenue',
  'BAF': 'Bafoussam Centre',
  'LMB': 'Limbe Down Beach',
};

String mb1Fcfa(int amount) {
  final s = amount.toString();
  final out = StringBuffer();
  for (var i = 0; i < s.length; i++) {
    if (i > 0 && (s.length - i) % 3 == 0) out.write(',');
    out.write(s[i]);
  }
  return '$out FCFA';
}
