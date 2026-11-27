// A customer the sales rep visits. Wholesale customers are shops and chop bars
// that buy in bulk; walk-in customers buy for themselves.
class Customer {
  const Customer({
    required this.id,
    required this.name,
    required this.type,
    required this.town,
    required this.phone,
    required this.homeBranch,
  });

  final int id;
  final String name;
  final String type; // 'wholesale' or 'walk_in'
  final String town;
  final String phone; // practice numbers only: 600 000 0xx is not a real line
  final String homeBranch; // the branch that usually supplies them, e.g. 'CAV'

  bool get isWholesale => type == 'wholesale';
}
