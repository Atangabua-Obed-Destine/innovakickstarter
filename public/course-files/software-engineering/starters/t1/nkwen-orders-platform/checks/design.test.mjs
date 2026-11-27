// Self-checks for "System Design". From the repository root: node --test "checks/design.test.mjs"
// They check that the design documents are complete. Whether the design is good is for your
// squad, your reviewers and the mentor to judge.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { read, list, todoLines, section, bullets, tableRows, mermaid, words } from './docs.mjs';

test('docs/architecture.md: a full diagram, the connections, and at least 3 decisions', () => {
  const text = read('docs/architecture.md');
  assert.deepEqual(todoLines(text), [], 'Replace every TODO in docs/architecture.md');
  const arrows = mermaid(text).split('\n').filter((l) => l.includes('-->') || l.includes('-.->'));
  assert.ok(arrows.length >= 4, `The diagram has ${arrows.length} arrow(s); show every part and who uses it (at least 4)`);
  assert.ok(tableRows(section(text, 'Who talks to whom')).length >= 2, 'Who talks to whom: at least 2 rows');
  assert.ok(bullets(section(text, 'Decisions')).length >= 3, 'Decisions: at least 3 bullet points, each with a reason');
});

test('docs/schema.md: the order tables are drawn and the questions answered', () => {
  const text = read('docs/schema.md');
  assert.deepEqual(todoLines(text), [], 'Replace every TODO in docs/schema.md');
  const diagram = mermaid(text).toUpperCase();
  assert.match(diagram, /ERDIAGRAM/, 'Keep the diagram an erDiagram');
  for (const table of ['BRANCHES', 'PRODUCTS', 'STOCK', 'ORDERS', 'ORDER_ITEMS', 'USERS']) {
    assert.match(diagram, new RegExp(`\\b${table}\\b`), `The diagram needs a ${table} table`);
  }
  const answers = section(text, 'Questions to answer').split('\n').filter((l) => /^\d+\./.test(l));
  assert.equal(answers.length, 4, 'Keep the 4 numbered questions');
  for (const a of answers) assert.ok(words(a.split('?').slice(1).join('?')) >= 5, `Answer in a full sentence: ${a.slice(0, 60)}`);
});

test('docs/wireframes: at least 2 screens, drawn or linked', () => {
  const text = read('docs/wireframes/README.md');
  assert.deepEqual(todoLines(text), [], 'Replace the TODO in docs/wireframes/README.md');
  const pictures = list('docs/wireframes/').filter((f) => /\.(png|jpe?g|webp|svg)$/i.test(f));
  const links = text.match(/https?:\/\/\S+/g) ?? [];
  assert.ok(pictures.length + links.length >= 2, `Found ${pictures.length} picture(s) and ${links.length} link(s); add at least 2 screens`);
});

test('contract/README.md: agreed by the squad, with the decisions made', () => {
  const text = read('contract/README.md');
  assert.deepEqual(todoLines(text), [], 'Replace every TODO in contract/README.md');
  const agreed = (text.match(/^Agreed by:(.*)$/m) ?? [])[1] ?? '';
  assert.ok((agreed.match(/@[A-Za-z0-9-]+/g) ?? []).length >= 2, 'Agreed by: the @usernames of the whole squad');
  assert.match(text, /^Date agreed:\s*\d{4}-\d{2}-\d{2}/m, 'Date agreed: YYYY-MM-DD');
});
