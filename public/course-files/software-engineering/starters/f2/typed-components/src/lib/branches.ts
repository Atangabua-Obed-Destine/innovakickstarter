import type { OpenBranchCode } from '../types.ts';

export const OPEN_CODES: readonly OpenBranchCode[] = ['NKW', 'ML4', 'CAV', 'BAF'];

// TODO 1: a type guard. A <select> always gives you a string. Return true only
// when value is one of OPEN_CODES; then TypeScript lets you use it as an OpenBranchCode.
export function isOpenBranchCode(value: string): value is OpenBranchCode {
  throw new Error(`TODO 1: isOpenBranchCode('${value}')`);
}
