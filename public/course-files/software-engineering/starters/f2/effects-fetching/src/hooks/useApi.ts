import { useEffect, useState } from 'react';
import { getJson } from '../api/nkwen.ts';

// The three things a request can be doing. A "discriminated union": the
// status field tells TypeScript which other fields exist.
export type ApiState<T> =
  | { status: 'loading' }
  | { status: 'error'; message: string }
  | { status: 'ready'; data: T };

// TODO 1: a custom hook that loads one URL and tells the component how it is going.
// - Keep an ApiState<T> in state, starting as { status: 'loading' }.
// - Keep an "attempt" number in state too. retry() adds 1 to it.
// - In useEffect (dependencies: [url, attempt]):
//     1. let ignore = false;  and set the state to loading
//     2. getJson<T>(url).then(...) -> if (!ignore) set { status: 'ready', data }
//        .catch(...)              -> if (!ignore) set { status: 'error', message }
//     3. return a cleanup function that sets ignore = true
// - Return { ...state, retry }.
export function useApi<T>(url: string): ApiState<T> & { retry: () => void } {
  console.log('TODO 1: load', url);
  return { status: 'loading', retry: () => {} };
}
