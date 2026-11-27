// Nkwen Traders branches. Limbe (LMB) is planned for 2027 and not open yet.
const openBranchCodes = ['NKW', 'ML4', 'CAV', 'BAF'];

const branchNames = {
  'NKW': 'Nkwen',
  'ML4': 'Mile 4',
  'CAV': 'Commercial Avenue',
  'BAF': 'Bafoussam Centre',
  'LMB': 'Limbe Down Beach',
};

// 'ML4' -> 'Mile 4'. An unknown code is shown as it is.
String branchName(String code) => branchNames[code] ?? code;
