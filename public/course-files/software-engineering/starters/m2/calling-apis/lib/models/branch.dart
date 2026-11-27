// One Nkwen Traders branch, as GET /branches describes it.
class Branch {
  const Branch({
    required this.code,
    required this.name,
    required this.city,
    required this.region,
    required this.manager,
    required this.openedYear,
    required this.status,
  });

  final String code; // the id, e.g. 'NKW'
  final String name;
  final String city;
  final String region;
  final String manager;
  final int openedYear;
  final String status; // 'open' or 'planned'

  bool get isOpen => status == 'open';

  // TODO 7: build a Branch from one JSON object. The JSON key for openedYear is 'opened_year'.
  factory Branch.fromJson(Map<String, dynamic> json) {
    throw UnimplementedError('TODO 7: Branch.fromJson');
  }
}
