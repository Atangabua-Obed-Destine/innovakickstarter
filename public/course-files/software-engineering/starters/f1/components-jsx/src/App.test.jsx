// Self-check for the whole page. It passes when TODO 1 to 4 are all done.
import { render, screen } from '@testing-library/react';
import { expect, test } from 'vitest';
import App from './App.jsx';

test('the page has a header, a main area and a footer', () => {
  render(<App />);
  expect(screen.getByRole('banner')).toBeInTheDocument();
  expect(screen.getByRole('main')).toBeInTheDocument();
  expect(screen.getByRole('contentinfo')).toBeInTheDocument();
});

test('the page shows exactly one product card', () => {
  render(<App />);
  expect(screen.getAllByRole('article')).toHaveLength(1);
});
