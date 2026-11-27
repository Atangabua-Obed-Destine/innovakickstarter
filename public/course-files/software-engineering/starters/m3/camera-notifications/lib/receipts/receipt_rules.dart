/// Rules for receipt photos. Reps pay for their own data, and photos are
/// uploaded when the order syncs, so we keep them small.
library;

/// Settings we pass to image_picker: it shrinks and compresses the photo for us.
const receiptMaxWidth = 1280.0; // pixels: still easy to read a signature
const receiptQuality = 70; // JPEG quality, 0 to 100

/// The largest photo we accept, in bytes (500 KB).
const receiptMaxBytes = 500 * 1024;

/// receipt_<orderId>_<yyyyMMdd-HHmm>.jpg, for example
/// receipt_ORD-7Q2K9X1B_20261203-1015.jpg
String receiptFileName(String orderId, DateTime takenAt) {
  // TODO 5: return receipt_<orderId>_<yyyyMMdd-HHmm>.jpg
  //         Months, days, hours and minutes always have 2 digits (padLeft).
  throw UnimplementedError('TODO: write receiptFileName() (step 5)');
}

/// A kind message if the photo cannot be used, or null if it is fine.
String? receiptProblem(int bytes) {
  if (bytes <= 0) return 'The photo is empty. Please take it again.';
  if (bytes > receiptMaxBytes) {
    final kb = (bytes / 1024).round();
    return 'The photo is too big ($kb KB). Please take it again: it must be under 500 KB.';
  }
  return null;
}
