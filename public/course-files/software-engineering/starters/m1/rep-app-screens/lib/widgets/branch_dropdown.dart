import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import '../data/branches.dart';
import '../state/order_draft.dart';

// Choose the branch the order comes from. It changes the shared OrderDraft,
// so the product list and the order draft both follow it.
class BranchDropdown extends StatelessWidget {
  const BranchDropdown({super.key});

  @override
  Widget build(BuildContext context) {
    // TODO A: return a DropdownButton<String> with:
    //   key: const ValueKey('branch-dropdown')
    //   value: the draft's branch (watch the draft)
    //   isExpanded: true
    //   items: one DropdownMenuItem per code in openBranchCodes, showing branchName(code)
    //   onChanged: set the branch on the draft (use context.read inside the callback)
    return const Text('TODO: branch dropdown');
  }
}
