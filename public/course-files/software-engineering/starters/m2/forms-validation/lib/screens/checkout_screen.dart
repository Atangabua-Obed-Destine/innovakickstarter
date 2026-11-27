import 'package:flutter/services.dart';
import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';

import '../data/branches.dart';
import '../data/payment_methods.dart';
import '../format.dart';
import '../state/order_draft.dart';
import '../validation/order_rules.dart';

// What the checkout form gives back when every field is valid.
class CheckoutDetails {
  const CheckoutDetails({required this.customerName, this.customerPhone, required this.paymentMethod, required this.branch});

  final String customerName;
  final String? customerPhone; // normalised, e.g. '600000101', or null
  final String paymentMethod;
  final String branch;
}

// The last step before an order is saved. It pops with CheckoutDetails, or null after Back.
class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key, this.serverErrors = const {}});

  // Messages from a 422 answer (see fieldErrorsFrom422), shown under their fields.
  final Map<String, String> serverErrors;

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _name;
  late final TextEditingController _phone;
  late String _payment;
  late String _branch;
  late Map<String, String> _serverErrors;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final draft = context.read<OrderDraft>();
    _name = TextEditingController(text: draft.customer?.name ?? '');
    _phone = TextEditingController(text: draft.customer?.phone ?? '');
    _payment = draft.paymentMethod;
    _branch = draft.branch;
    _serverErrors = Map.of(widget.serverErrors);
  }

  @override
  void dispose() {
    // Controllers hold resources: free them when the screen closes.
    _name.dispose();
    _phone.dispose();
    super.dispose();
  }

  void _submit() {
    // TODO 6: if _saving is true, return (a second tap must do nothing).
    // TODO 7: ask the Form to check every field: if (!_formKey.currentState!.validate()) return;
    // TODO 8: set _saving to true inside setState, then pop with the details:
    Navigator.pop(
      context,
      CheckoutDetails(
        customerName: _name.text.trim(),
        customerPhone: _phone.text, // TODO 9: send normalisePhone(_phone.text) instead
        paymentMethod: _payment,
        branch: _branch,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final draft = context.watch<OrderDraft>();
    final otherErrors = _serverErrors.entries
        .where((e) => !['customer_name', 'payment_method', 'branch'].contains(e.key))
        .toList();
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: Form(
        key: _formKey,
        autovalidateMode: AutovalidateMode.onUserInteraction,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (otherErrors.isNotEmpty)
              Card(
                key: const ValueKey('server-errors'),
                color: Theme.of(context).colorScheme.errorContainer,
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [for (final e in otherErrors) Text(e.value)],
                  ),
                ),
              ),
            TextFormField(
              key: const ValueKey('customer-name'),
              controller: _name,
              decoration: InputDecoration(
                labelText: 'Customer name',
                border: const OutlineInputBorder(),
                errorText: _serverErrors['customer_name'],
              ),
              textCapitalization: TextCapitalization.words,
              textInputAction: TextInputAction.next,
              // TODO 10: validator: validateCustomerName,
              onChanged: (_) {
                if (_serverErrors.containsKey('customer_name')) {
                  setState(() => _serverErrors.remove('customer_name'));
                }
              },
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              key: const ValueKey('checkout-payment'),
              initialValue: _payment,
              isExpanded: true, // long labels such as 'MTN Mobile Money' must not overflow a small phone
              decoration: const InputDecoration(labelText: 'Payment method', border: OutlineInputBorder()),
              items: [
                for (final entry in paymentMethodLabels.entries)
                  DropdownMenuItem(value: entry.key, child: Text(entry.value)),
              ],
              onChanged: (value) => setState(() => _payment = value ?? _payment),
            ),
            const SizedBox(height: 16),
            TextFormField(
              key: const ValueKey('customer-phone'),
              controller: _phone,
              decoration: InputDecoration(
                labelText: phoneRequiredFor(_payment) ? 'Customer phone (needed for mobile money)' : 'Customer phone (optional)',
                hintText: '6XX XX XX XX',
                border: const OutlineInputBorder(),
              ),
              // TODO 11: open the phone keypad (keyboardType: TextInputType.phone),
              //          allow only digits, spaces and + :
              //            inputFormatters: [FilteringTextInputFormatter.allow(RegExp(r'[0-9 +]'))],
              //          and add a validator that calls validatePhone(value, paymentMethod: _payment).
              textInputAction: TextInputAction.done,
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              key: const ValueKey('checkout-branch'),
              initialValue: _branch,
              isExpanded: true,
              decoration: const InputDecoration(labelText: 'Branch', border: OutlineInputBorder()),
              items: [
                for (final code in openBranchCodes) DropdownMenuItem(value: code, child: Text(branchName(code))),
              ],
              onChanged: (value) => setState(() => _branch = value ?? _branch),
            ),
            const SizedBox(height: 16),
            Text('${draft.itemCount} items · estimated ${formatFcfa(draft.totalFcfa)}'),
            const SizedBox(height: 16),
            FilledButton(
              key: const ValueKey('save-order'),
              onPressed: _submit, // TODO 12: disable the button while _saving (onPressed: null)
              child: const Text('Save order'),
            ),
          ],
        ),
      ),
    );
  }
}
