// Self-check for TODO 1. If this whole file fails with an error that points
// at BranchNotice.jsx, the JSX still has a mistake that stops it compiling.
import { render, screen } from '@testing-library/react';
import { afterEach, expect, test, vi } from 'vitest';
import BranchNotice from './components/BranchNotice.jsx';

afterEach(() => {
  vi.restoreAllMocks();
});

test('renders without any React warning', () => {
  const warn = vi.spyOn(console, 'error').mockImplementation(() => {});
  render(<BranchNotice />);
  const messages = warn.mock.calls.map((call) => String(call[0]));
  expect(messages, 'React printed a warning. Read it: it names the JSX rule').toEqual([]);
});

test('the heading and the panel are both there', () => {
  const { container } = render(<BranchNotice />);
  expect(screen.getByRole('heading', { name: /Bafoussam Centre/ })).toBeInTheDocument();
  expect(container.querySelector('section.panel'), 'The <section> needs the class "panel"').not.toBeNull();
});

test('the label is linked to the checkbox', () => {
  render(<BranchNotice />);
  const box = screen.getByLabelText('Tell me when Limbe opens');
  expect(box).toHaveAttribute('type', 'checkbox');
});

test('the opening line is bold', () => {
  render(<BranchNotice />);
  expect(screen.getByText(/Opened in April 2026/)).toHaveStyle({ fontWeight: 'bold' });
});
