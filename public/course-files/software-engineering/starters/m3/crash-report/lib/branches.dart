/// The branches, as the app downloads them from GET /branches.
const branches = [
  {'code': 'NKW', 'name': 'Nkwen'},
  {'code': 'ML4', 'name': 'Mile 4'},
  {'code': 'CAV', 'name': 'Commercial Avenue'},
  {'code': 'BAF', 'name': 'Bafoussam Centre'},
  {'code': 'LMB', 'name': 'Limbe Down Beach'},
];

/// The branch name shown on an order card, from its code.
String branchName(String code) {
  final branch = branches.firstWhere((b) => b['code'] == code);
  return branch['name']!;
}
