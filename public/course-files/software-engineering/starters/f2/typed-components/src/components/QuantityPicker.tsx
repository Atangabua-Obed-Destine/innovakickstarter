// TODO 5: a drop-down of quantities from 1 to max.
// Type the props: label (string), value (number), max (number), and onChange,
// a function that receives the new quantity AS A NUMBER: (quantity: number) => void
// Type the event too:  import type { ChangeEvent } from 'react';
//   function handleChange(event: ChangeEvent<HTMLSelectElement>) { ... }
// Once onChange says it wants a number, TypeScript points at a bug below. Fix it.
export default function QuantityPicker({ label, value, max, onChange }) {
  const choices = Array.from({ length: Math.max(max, value) }, (_, i) => i + 1);
  function handleChange(event) {
    onChange(event.target.value);
  }
  return (
    <label className="field">
      {label}
      <select value={value} onChange={handleChange}>
        {choices.map((n) => (
          <option key={n} value={n}>{n}</option>
        ))}
      </select>
    </label>
  );
}
