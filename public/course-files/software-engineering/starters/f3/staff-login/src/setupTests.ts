// Runs before every test file.
// 1. Adds matchers such as toBeInTheDocument() and toBeDisabled() to expect().
import '@testing-library/jest-dom/vitest'
// 2. Removes what each test drew, so tests cannot affect each other.
import { cleanup } from '@testing-library/react'
import { afterEach, vi } from 'vitest'

afterEach(() => {
  cleanup()
  vi.restoreAllMocks()
  vi.unstubAllGlobals()
})
