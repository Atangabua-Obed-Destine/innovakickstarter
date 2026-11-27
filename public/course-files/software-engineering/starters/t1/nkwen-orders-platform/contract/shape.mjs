// Compares the SHAPE of two JSON values: the same keys, with the same kinds of value.
// The values themselves may differ (ids, dates, totals), and extra keys are allowed,
// because ADDING a field does not break the other teams. A missing key, a renamed key
// or a different kind of value (text instead of a number) does break them.
//
// shapeProblems(expected, actual) returns a list of problems; an empty list means "same shape".

export function kindOf(value) {
  if (value === null) return 'null';
  if (Array.isArray(value)) return 'array';
  if (typeof value === 'number') return Number.isInteger(value) ? 'whole number' : 'decimal number';
  return typeof value; // 'string', 'boolean' or 'object'
}

export function shapeProblems(expected, actual, path = 'body') {
  const want = kindOf(expected);
  const got = kindOf(actual);
  if (want !== got) return [`${path} should be a ${want}, but it is a ${got} (${JSON.stringify(actual)?.slice(0, 40)})`];
  const problems = [];
  if (want === 'array' && expected.length > 0 && actual.length > 0) {
    // Lists: every item must have the shape of the example's first item.
    actual.forEach((item, i) => problems.push(...shapeProblems(expected[0], item, `${path}[${i}]`)));
  }
  if (want === 'object') {
    for (const key of Object.keys(expected)) {
      if (!(key in actual)) problems.push(`${path}.${key} is missing`);
      else problems.push(...shapeProblems(expected[key], actual[key], `${path}.${key}`));
    }
  }
  return problems;
}
