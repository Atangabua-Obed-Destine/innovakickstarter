import { useEffect, useState } from 'react';
import { getBranches, getCategories, getProducts, practiceParams } from './nkwen.js';

// A custom hook (ready for you). A hook is a function whose name starts with
// "use" and that uses React features such as state. This one loads the
// products, the OPEN branches and the categories from the API when the page
// opens, again when the practice mode changes, and again when you call retry().
// In Chapter 8 ("Effects & Data Fetching") you will write hooks like this yourself.
//
// It returns { status, products, branches, categories, error, retry }
//   status is 'loading', 'ready' or 'error'.
export function useCatalogue(mode = 'normal') {
  const [attempt, setAttempt] = useState(0);
  const [result, setResult] = useState({ status: 'loading', products: [], branches: [], categories: [], error: null });

  useEffect(() => {
    let ignore = false; // set to true if the page no longer wants this answer
    const params = practiceParams(mode);
    setResult((old) => ({ ...old, status: 'loading', error: null }));
    Promise.all([getProducts(params), getBranches(params), getCategories(params)])
      .then(([products, branches, categories]) => {
        if (ignore) return;
        setResult({ status: 'ready', products, branches: branches.filter((b) => b.status === 'open'), categories, error: null });
      })
      .catch((error) => {
        if (ignore) return;
        setResult((old) => ({ ...old, status: 'error', error }));
      });
    return () => {
      ignore = true;
    };
  }, [mode, attempt]);

  return { ...result, retry: () => setAttempt((n) => n + 1) };
}
