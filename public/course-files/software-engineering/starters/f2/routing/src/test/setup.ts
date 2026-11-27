// Runs before every test file: adds matchers such as toBeInTheDocument()
// and cleans the pretend page and localStorage after each test.
import '@testing-library/jest-dom/vitest';
import { cleanup } from '@testing-library/react';
import { afterEach } from 'vitest';

afterEach(() => {
  cleanup();
  localStorage.clear();
});
