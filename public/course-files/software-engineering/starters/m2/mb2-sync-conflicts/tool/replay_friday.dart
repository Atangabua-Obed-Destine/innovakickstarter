// Replays the six orders from Friday's sync against the Nkwen practice API.
// Run it from your project folder:
//   dart run tool/replay_friday.dart
//   dart run tool/replay_friday.dart http://127.0.0.1:8765/api/course/nkwen   (a local copy)
// The practice API checks and prices each order, but saves nothing.
// It answers at most 30 orders a minute: run this script once, then wait a minute.
import 'dart:convert';
import 'dart:math';

import 'package:http/http.dart' as http;

const defaultBase = 'https://innovakickstarter.com/api/course/nkwen';

// What Ms. Bih Doris's phone held when she found a network at Mile 4.
// "estimate" is the total the phone showed the customer (from its saved price list).
final friday = <Map<String, dynamic>>[
  {
    'case': 1, 'estimate': 72010, 'sendTwice': true,
    'body': {'branch': 'ML4', 'customer_name': 'Mami Siri Chop Bar', 'payment_method': 'cash', 'items': [{'product_id': 6, 'quantity': 2}]},
  },
  {
    'case': 2, 'estimate': 190550,
    'body': {'branch': 'NKW', 'customer_name': 'Tamfu & Sons Store', 'payment_method': 'mtn_momo', 'items': [{'product_id': 5, 'quantity': 10}]},
  },
  {
    'case': 3, 'estimate': 7600,
    'body': {'branch': 'LMB', 'customer_name': 'Ngwa Mini-Market', 'payment_method': 'cash', 'items': [{'product_id': 9, 'quantity': 10}]},
  },
  {
    'case': 4, 'estimate': 150800,
    'body': {'branch': 'BAF', 'customer_name': 'Kamga Alimentation', 'payment_method': 'orange_money', 'items': [{'product_id': 15, 'quantity': 20}, {'product_id': 9, 'quantity': 30}]},
  },
  {
    'case': 5, 'estimate': 1800,
    'body': {'branch': 'NKW', 'customer_name': 'A', 'payment_method': 'cash', 'items': [{'product_id': 1, 'quantity': 3}]},
  },
  {
    'case': 6, 'estimate': 12875, 'query': '?fail=503',
    'body': {'branch': 'BAF', 'customer_name': 'Djoumessi Brigitte', 'payment_method': 'cash', 'items': [{'product_id': 19, 'quantity': 5}]},
  },
];

// A fresh version 4 UUID, like the phone makes for each order. The server remembers
// a key for 24 hours, so every run of this script uses new keys.
String newKey() {
  final r = Random.secure();
  final b = List<int>.generate(16, (_) => r.nextInt(256));
  b[6] = (b[6] & 0x0f) | 0x40;
  b[8] = (b[8] & 0x3f) | 0x80;
  final h = b.map((x) => x.toRadixString(16).padLeft(2, '0')).join();
  return '${h.substring(0, 8)}-${h.substring(8, 12)}-${h.substring(12, 16)}-${h.substring(16, 20)}-${h.substring(20)}';
}

final client = http.Client();

// Send one order and print one line: the status, the order id (or the error), and
// whether the server replayed an earlier answer (header Idempotent-Replayed: true).
Future<void> send(String base, String label, Map<String, dynamic> body, {String? key, String query = '', int? estimate}) async {
  final headers = {'Content-Type': 'application/json', 'Accept': 'application/json', if (key != null) 'Idempotency-Key': key};
  try {
    final r = await client.post(Uri.parse('$base/orders$query'), headers: headers, body: jsonEncode(body));
    final json = jsonDecode(utf8.decode(r.bodyBytes)) as Map<String, dynamic>;
    final o = json['order'] ?? json['data'];
    final replayed = r.headers['idempotent-replayed'] == 'true' ? '  (replayed)' : '';
    final detail = o is Map
        ? 'id ${o['id']}, server total ${o['total_fcfa']} FCFA${estimate == null ? '' : ', phone estimate $estimate FCFA'}'
        : jsonEncode(json);
    print('$label: ${r.statusCode}  $detail$replayed');
  } on http.ClientException catch (e) {
    print('$label: no answer (${e.message})');
  }
}

Future<void> main(List<String> args) async {
  final base = args.isNotEmpty ? args.first : defaultBase;
  print('Replaying Friday against $base\n');
  for (final order in friday) {
    final key = newKey(); // the order's localId on the phone
    final body = order['body'] as Map<String, dynamic>;
    final n = order['case'];
    final query = (order['query'] as String?) ?? '';
    final estimate = order['estimate'] as int?;
    if (order['sendTwice'] == true) {
      await send(base, 'Case $n (send 1)', body, key: key, estimate: estimate);
      await send(base, 'Case $n (send 2, same key)', body, key: key, estimate: estimate);
    } else {
      await send(base, 'Case $n', body, key: key, query: query, estimate: estimate);
    }
  }
  // Three experiments with case 1, to see why the key matters.
  final case1 = friday.first['body'] as Map<String, dynamic>;
  print('\nExperiments with case 1:');
  await send(base, 'A. no key, send 1', case1);
  await send(base, 'A. no key, send 2', case1);
  final reused = newKey();
  await send(base, 'B. new key, first order', case1, key: reused);
  final changed = {...case1, 'items': [{'product_id': 6, 'quantity': 1}]};
  await send(base, 'B. same key, a DIFFERENT order', changed, key: reused);
  client.close();
  print('\nWrite what each answer means, and what the app should do, in CONFLICTS.md.');
}
