/// Formats whole francs the Nkwen way: 78610 -> "78,610 FCFA".
/// Prices already include VAT (19.25%), so we never add it here.
String formatFcfa(int amount) {
  final digits = amount.abs().toString();
  final out = StringBuffer();
  for (var i = 0; i < digits.length; i++) {
    final left = digits.length - i;
    if (i > 0 && left % 3 == 0) out.write(',');
    out.write(digits[i]);
  }
  return '${amount < 0 ? '-' : ''}$out FCFA';
}
