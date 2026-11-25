// Self-check for validation.js. Run in this folder:  node --test
const test = require('node:test');
const assert = require('node:assert/strict');
const { normalisePhone, isValidCameroonPhone, charactersLeft, validateCallback } = require('./validation.js');

test('normalisePhone removes spaces, dots and dashes', () => {
  assert.equal(normalisePhone('699 00 11 22'), '699001122');
  assert.equal(normalisePhone('699-00.11 22'), '699001122');
});

test('normalisePhone removes the +237 country code', () => {
  assert.equal(normalisePhone('+237 699 00 11 22'), '699001122');
  assert.equal(normalisePhone('00237699001122'), '699001122', '00237 means the same as +237');
});

test('isValidCameroonPhone accepts 9 digits starting with 6', () => {
  assert.equal(isValidCameroonPhone('699 00 11 22'), true);
  assert.equal(isValidCameroonPhone('+237699001122'), true);
});

test('isValidCameroonPhone rejects wrong numbers', () => {
  assert.equal(isValidCameroonPhone('69900112'), false, '8 digits is too short');
  assert.equal(isValidCameroonPhone('6990011223'), false, '10 digits is too long');
  assert.equal(isValidCameroonPhone('599001122'), false, 'A mobile number starts with 6');
  assert.equal(isValidCameroonPhone('69900112a'), false, 'Letters are not digits');
  assert.equal(isValidCameroonPhone(''), false, 'An empty box is not a number');
});

test('charactersLeft counts down and can go below zero', () => {
  assert.equal(charactersLeft('Bread', 10), 5);
  assert.equal(charactersLeft('', 10), 10);
  assert.ok(charactersLeft('x'.repeat(12), 10) < 0, 'Too long means a negative number');
});

test('validateCallback returns {} for a good form', () => {
  const good = { name: 'Ngwa Brenda', phone: '699 00 11 22', branch: 'ML4', message: 'Please call me about rice prices.' };
  assert.deepEqual(validateCallback(good), {});
});

test('validateCallback names each problem field', () => {
  const errors = validateCallback({ name: ' ', phone: '12345', branch: '', message: 'x'.repeat(201) });
  for (const field of ['name', 'phone', 'branch', 'message']) {
    assert.equal(typeof errors[field], 'string', `errors.${field} should be a message for the customer`);
    assert.ok(errors[field].length > 5, `errors.${field} should explain the problem in words`);
  }
});

test('validateCallback does not complain about good fields', () => {
  const errors = validateCallback({ name: 'Ngwa Brenda', phone: 'abc', branch: 'BAF', message: '' });
  assert.deepEqual(Object.keys(errors), ['phone'], 'Only the phone is wrong here');
});
