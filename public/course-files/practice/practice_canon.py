"""Practice Lab answer canonicalisation (runs in the browser via Pyodide, and offline to
compute expected answers). A fellow's `answer` is turned into a canonical JSON string; the
server compares a hash of it with the expected one, so answers never travel to the page.

Rules: numbers rounded to 2 decimals with floor(x * 100 + 0.5) / 100 (identical in JavaScript,
unlike round(), which rounds exact halves to even); whole numbers become int; strings trimmed;
dicts and pandas Series become key-sorted objects (order does not matter);
lists, tuples, numpy arrays and DataFrames (as row lists) keep their order.
"""
import json
import math


def _round2(x: float):
    r = math.floor(float(x) * 100 + 0.5) / 100
    return int(r) if r == int(r) else r


def _canon(v):
    try:
        import numpy as np
        import pandas as pd

        if isinstance(v, pd.DataFrame):
            v = v.values.tolist()
        elif isinstance(v, pd.Series):
            v = {str(k): x for k, x in v.items()}
        elif isinstance(v, pd.Index):
            v = v.tolist()
        elif isinstance(v, np.ndarray):
            v = v.tolist()
        elif isinstance(v, np.generic):
            v = v.item()
        elif v is pd.NA or v is pd.NaT:
            v = None
    except ImportError:
        pass
    if v is None or isinstance(v, bool):
        return v
    if isinstance(v, (int, float)):
        if isinstance(v, float) and not math.isfinite(v):
            return None
        return _round2(v)
    if isinstance(v, str):
        return v.strip()
    if isinstance(v, dict):
        return {str(k).strip(): _canon(x) for k, x in v.items()}
    if isinstance(v, (list, tuple, set)):
        items = [_canon(x) for x in v]
        return sorted(items, key=lambda x: json.dumps(x, sort_keys=True)) if isinstance(v, set) else items
    return str(v).strip()


def canonical(v) -> str:
    return json.dumps(_canon(v), separators=(",", ":"), ensure_ascii=False, sort_keys=True)


def canonical_sql_rows(rows, ordered: bool) -> str:
    """Mirror of the browser's SQL result canonicalisation (practice-lab.js)."""
    def cell(x):
        if x is None:
            return None
        if isinstance(x, (int, float)) and not isinstance(x, bool):
            if isinstance(x, float) and not math.isfinite(x):
                return None
            return _round2(x)
        return str(x).strip()

    row_strings = [json.dumps([cell(x) for x in row], separators=(",", ":"), ensure_ascii=False) for row in rows]
    if not ordered:
        row_strings.sort()
    return "[" + ",".join(row_strings) + "]"
