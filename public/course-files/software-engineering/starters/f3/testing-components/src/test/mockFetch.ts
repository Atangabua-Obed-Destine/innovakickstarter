// Helpers that replace the browser's fetch() inside a test.
// The component calls fetch as usual, but gets our answer instead of the network's.
import { vi } from 'vitest'

type Answer = { status: number; body: unknown }

function reply({ status, body }: Answer) {
  return new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } })
}

// Every call to fetch answers with this status and JSON body.
export function mockFetch(status: number, body: unknown) {
  const fake = vi.fn(async () => reply({ status, body }))
  vi.stubGlobal('fetch', fake)
  return fake
}

// The first call answers with `first`; every later call answers with `then`.
// Good for testing a "Try again" button.
export function mockFetchSequence(first: Answer, then: Answer) {
  let calls = 0
  const fake = vi.fn(async () => (calls++ === 0 ? reply(first) : reply(then)))
  vi.stubGlobal('fetch', fake)
  return fake
}
