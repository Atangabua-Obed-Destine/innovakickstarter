// Talks to the live Nkwen Traders course API (ready for you).
// It is the same idea as your Chapter 5 nkwen-api.js, as an ES module.
// VITE_NKWEN_API lets a team point the app at another copy of the API
// (you meet environment variables in Chapter 9). You can ignore it.
export const API_BASE = import.meta.env.VITE_NKWEN_API ?? 'https://innovakickstarter.com/api/course/nkwen';

export function buildUrl(path, params = {}) {
  const query = new URLSearchParams();
  for (const [name, value] of Object.entries(params)) {
    if (value !== undefined && value !== null && value !== '') query.append(name, value);
  }
  const text = query.toString();
  return text ? `${API_BASE}${path}?${text}` : `${API_BASE}${path}`;
}

// Returns the JSON body, or throws an Error with a .status (0 = no answer at all).
export async function getJson(path, params = {}) {
  let response;
  try {
    response = await fetch(buildUrl(path, params));
  } catch {
    const error = new Error('Could not reach the Nkwen API. Check your internet connection.');
    error.status = 0;
    throw error;
  }
  const body = await response.json().catch(() => null);
  if (!response.ok) {
    const error = new Error(`Request failed (${response.status}): ${body?.message ?? response.statusText}`);
    error.status = response.status;
    throw error;
  }
  return body;
}

export async function getProducts(params) {
  return (await getJson('/products', params)).products;
}

export async function getBranches(params) {
  return (await getJson('/branches', params)).branches;
}

export async function getCategories(params) {
  return (await getJson('/categories', params)).categories;
}

// The "Practice mode" drop-down uses the API's teaching switches.
export function practiceParams(mode) {
  if (mode === 'slow') return { delay: 2500 };
  if (mode === 'error') return { fail: 500 };
  return {};
}
