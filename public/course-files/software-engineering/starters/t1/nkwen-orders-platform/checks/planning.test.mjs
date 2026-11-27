// Self-checks for "Sprint Planning". From the repository root: node --test "checks/planning.test.mjs"
// They check that your planning documents are complete and consistent. They cannot tell you
// whether your choices are wise: that is what the mentor and your squad are for.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { read, todoLines, section, bullets, tableRows, words } from './docs.mjs';

const PATHWAYS = ['backend', 'frontend', 'mobile', 'fullstack'];
const STARTER_IDEAS = ['when and how you hold the daily check-in', 'how fast you review a pull request you are asked to review',
  'what you do when you are stuck', 'how you decide when you disagree'];

test('docs/team.md: no TODO left', () => {
  assert.deepEqual(todoLines(read('docs/team.md')), [], 'Replace every TODO in docs/team.md');
});

test('docs/team.md: every member has a name, a GitHub username, a pathway and a squad role', () => {
  const rows = tableRows(section(read('docs/team.md'), 'Members'));
  assert.ok(rows.length >= 2, `A squad has at least 2 members; the table has ${rows.length} row(s)`);
  for (const [name, github, pathway, role] of rows) {
    assert.ok(name, 'a member has no name');
    assert.match(github ?? '', /^@[A-Za-z0-9-]+$/, `${name}: write the GitHub username like @amina-dev`);
    assert.ok(PATHWAYS.includes((pathway ?? '').toLowerCase()), `${name}: pathway must be one of ${PATHWAYS.join(', ')}`);
    assert.ok(role, `${name}: give at least one squad role`);
  }
});

test('docs/team.md: every missing pathway is covered or left out on purpose', () => {
  const text = read('docs/team.md');
  const have = tableRows(section(text, 'Members')).map((row) => (row[2] ?? '').toLowerCase());
  const answer = section(text, 'Missing pathways').split('\n').slice(1).filter((l) => !/\bTODO\b/.test(l)).join(' ').toLowerCase();
  const missing = ['backend', 'frontend', 'mobile'].filter((p) => !have.includes(p));
  if (missing.length === 0) assert.match(answer, /none/, 'You have all three pathways: write "None"');
  for (const p of missing) assert.ok(answer.includes(p), `Nobody's pathway is ${p}: say who covers ${p} work or what you leave out`);
});

test('docs/team.md: the working agreement has at least 4 rules of your own', () => {
  const rules = bullets(section(read('docs/team.md'), 'Working agreement'));
  const copied = rules.filter((r) => STARTER_IDEAS.some((idea) => r.toLowerCase().includes(idea)));
  assert.equal(copied.length, 0, `Turn the ideas into your own rules: ${copied.join(' | ')}`);
  assert.ok(rules.length >= 4, `Found ${rules.length} rule(s); write at least 4`);
});

test('docs/definition-of-done.md: at least 8 checks and no TODO', () => {
  const text = read('docs/definition-of-done.md');
  assert.deepEqual(todoLines(text), [], 'Replace the TODO line with your own rules');
  const boxes = text.split('\n').filter((line) => /^\s*- \[ \]\s+\S/.test(line));
  assert.ok(boxes.length >= 8, `Found ${boxes.length} checks; the squad needs at least 8 (the 5 given and 3 of your own)`);
});

test('docs/sprint-log.md: Sprint 1 is planned (date, facilitator, goal, stories)', () => {
  const sprint1 = section(read('docs/sprint-log.md'), 'Sprint 1');
  const line = (label) => (sprint1.split('\n').find((l) => l.startsWith(label)) ?? '').slice(label.length).trim();
  assert.match(line('Planned on:'), /^\d{4}-\d{2}-\d{2}$/, 'Planned on: write the date as YYYY-MM-DD');
  assert.match(line('Facilitator:'), /@[A-Za-z0-9-]+/, 'Facilitator: write their @username');
  const goal = line('Sprint goal:');
  assert.ok(!/\bTODO\b/.test(goal) && words(goal) >= 8, 'Sprint goal: one sentence of at least 8 words');
  const stories = line('Stories in this sprint:').match(/#\d+/g) ?? [];
  assert.ok(stories.length >= 3, `Stories in this sprint: list at least 3 issue numbers like #4 (found ${stories.length})`);
});
