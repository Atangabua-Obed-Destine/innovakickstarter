// Small helpers for the document self-checks in this folder.
import { readFileSync, existsSync, readdirSync } from 'node:fs';

const root = new URL('../', import.meta.url);

export const read = (path) => {
  const url = new URL(path, root);
  if (!existsSync(url)) throw new Error(`${path} is missing. Did you copy every starter file?`);
  return readFileSync(url, 'utf8').replace(/\r\n/g, '\n');
};

export const list = (path) => (existsSync(new URL(path, root)) ? readdirSync(new URL(path, root)) : []);

// The lines that still contain the word TODO.
export const todoLines = (text) => text.split('\n').filter((line) => /\bTODO\b/.test(line)).map((line) => line.trim());

// The text under "## <heading>" up to the next "## " heading.
export function section(text, heading) {
  const start = text.indexOf(`\n## ${heading}`);
  if (start === -1) return '';
  const rest = text.slice(start + 1);
  const end = rest.indexOf('\n## ', 1);
  return end === -1 ? rest : rest.slice(0, end);
}

export const bullets = (text) => text.split('\n').filter((line) => /^\s*[-*]\s+\S/.test(line) && !/^\s*[-*]\s+\[[ x]\]/.test(line)).map((l) => l.trim());

// The data rows of the first Markdown table in the text (no header, no |---| line), as lists of cells.
export function tableRows(text) {
  const lines = text.split('\n').filter((line) => line.trim().startsWith('|'));
  return lines.slice(2).map((line) => line.trim().replace(/^\||\|$/g, '').split('|').map((cell) => cell.trim()));
}

// The body of the first ```mermaid block.
export const mermaid = (text) => (text.match(/```mermaid\n([\s\S]*?)```/) || [])[1] || '';

export const words = (text) => text.trim().split(/\s+/).filter(Boolean).length;
