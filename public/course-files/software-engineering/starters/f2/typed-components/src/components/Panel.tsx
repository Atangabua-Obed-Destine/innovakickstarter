// TODO 3: Panel takes a title (text) and children (any JSX).
// The type for "any JSX" is ReactNode:  import type { ReactNode } from 'react';
export default function Panel({ title, children }) {
  return (
    <section className="panel">
      <h2>{title}</h2>
      {children}
    </section>
  );
}
