// Practice customers for the sales-rep app. The businesses are made up.
// The phone numbers start with 600 so they cannot reach a real person.
import '../models/customer.dart';

const sampleCustomers = <Customer>[
  Customer(id: 1, name: 'Mbah Clarisse Provisions', type: 'wholesale', town: 'Commercial Avenue, Bamenda', phone: '600000101', homeBranch: 'CAV'),
  Customer(id: 2, name: 'Tamfu & Sons Store', type: 'wholesale', town: 'Nkwen, Bamenda', phone: '600000102', homeBranch: 'NKW'),
  Customer(id: 3, name: 'Mami Siri Chop Bar', type: 'wholesale', town: 'Mile 4, Bamenda', phone: '600000103', homeBranch: 'ML4'),
  Customer(id: 4, name: 'Kamga Alimentation', type: 'wholesale', town: 'Bafoussam', phone: '600000104', homeBranch: 'BAF'),
  Customer(id: 5, name: 'Ngwa Mini-Market', type: 'wholesale', town: 'Bambili', phone: '600000105', homeBranch: 'NKW'),
  Customer(id: 6, name: 'Djoumessi Brigitte', type: 'walk_in', town: 'Bafoussam', phone: '600000106', homeBranch: 'BAF'),
];
