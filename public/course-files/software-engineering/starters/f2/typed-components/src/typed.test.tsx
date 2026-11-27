// Self-check for Typing React Components. Run BOTH:
//   npm run typecheck   (most of today's work: props.check.tsx must be happy)
//   npm test            (the components still behave)
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test, vi } from 'vitest';
import App from './App.tsx';
import BranchSelect from './components/BranchSelect.tsx';
import QuantityPicker from './components/QuantityPicker.tsx';
import { NKWEN } from './data/nkwen.ts';
import { isOpenBranchCode } from './lib/branches.ts';

describe('TODO 1: isOpenBranchCode', () => {
  test('knows the four open codes, and nothing else', () => {
    expect(['NKW', 'ML4', 'CAV', 'BAF'].every(isOpenBranchCode)).toBe(true);
    expect(['LMB', 'nkw', 'Nkwen', ''].some(isOpenBranchCode)).toBe(false);
  });
});

describe('TODO 5: QuantityPicker', () => {
  test('onChange receives a NUMBER, not text', async () => {
    const user = userEvent.setup();
    const onChange = vi.fn();
    render(<QuantityPicker label="Quantity of Rice 50kg" value={1} max={10} onChange={onChange} />);
    await user.selectOptions(screen.getByLabelText('Quantity of Rice 50kg'), '3');
    expect(onChange).toHaveBeenCalledWith(3);
  });
});

describe('TODO 6: BranchSelect', () => {
  test('passes the chosen open code to onChange', async () => {
    const user = userEvent.setup();
    const onChange = vi.fn();
    const open = NKWEN.branches.filter((b) => b.status === 'open');
    render(<BranchSelect branches={open} value="NKW" onChange={onChange} />);
    await user.selectOptions(screen.getByLabelText('Collect at'), 'BAF');
    expect(onChange).toHaveBeenCalledWith('BAF');
  });
});

describe('The whole page', () => {
  test('add, then choose a quantity: the quantity is a number', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.click(screen.getByRole('button', { name: /Add Rice 50kg/ }));
    const picker = screen.getByLabelText('Quantity of Rice 50kg');
    await user.selectOptions(picker, '4');
    await user.click(screen.getByRole('button', { name: /Rice 50kg/ }));
    expect(screen.getByLabelText('Quantity of Rice 50kg')).toHaveValue('5');
  });
});
