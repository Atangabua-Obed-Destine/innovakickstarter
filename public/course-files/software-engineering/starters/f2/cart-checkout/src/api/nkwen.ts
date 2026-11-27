// Talks to the live Nkwen Traders course API (ready for you).
// VITE_NKWEN_API lets a team point the app at another copy of the API
// (environment variables come in Chapter 9). You can ignore it.
export const API_BASE: string = import.meta.env.VITE_NKWEN_API ?? 'https://innovakickstarter.com/api/course/nkwen';

export type Params = Record<string, string | number | undefined>;

export function buildUrl(path: string, params: Params = {}): string {
  const query = new URLSearchParams();
  for (const [name, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') query.append(name, String(value));
  }
  const text = query.toString();
  return text ? `${API_BASE}${path}?${text}` : `${API_BASE}${path}`;
}

// An Error that also remembers the HTTP status (0 means no answer at all).
export class ApiError extends Error {
  status: number;
  constructor(message: string, status: number) {
    super(message);
    this.status = status;
  }
}

function messageOf(body: unknown, fallback: string): string {
  if (typeof body === 'object' && body !== null && 'message' in body && typeof body.message === 'string') return body.message;
  return fallback;
}

// GET a URL and return its JSON as type T. We trust the practice API's shape here;
// for data you do not trust, check it first (TypeScript Essentials, parseProduct).
export async function getJson<T>(url: string, signal?: AbortSignal): Promise<T> {
  let response: Response;
  try {
    response = await fetch(url, { signal });
  } catch (error) {
    if (signal?.aborted) throw error;
    throw new ApiError('Could not reach the Nkwen API. Check your internet connection.', 0);
  }
  const body: unknown = await response.json().catch(() => null);
  if (!response.ok) throw new ApiError(`Request failed (${response.status}): ${messageOf(body, response.statusText)}`, response.status);
  return body as T;
}

// The "Practice mode" drop-down uses the API's teaching switches.
export type PracticeMode = 'normal' | 'slow' | 'error';
export function practiceParams(mode: PracticeMode): Params {
  if (mode === 'slow') return { delay: 2500 };
  if (mode === 'error') return { fail: 500 };
  return {};
}
