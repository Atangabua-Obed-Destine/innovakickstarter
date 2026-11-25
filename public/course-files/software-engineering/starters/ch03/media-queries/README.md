# Media Queries: make the branches page mobile-first

`style.css` was written desktop-first. It looks fine on a laptop and squashed on a phone. You will rewrite only the **layout** part, mobile-first.

## Steps
1. Open `index.html`. In DevTools device mode, look at it at 360px. Write down what is squashed or cut off.
2. Read the comment block marked `LAYOUT` at the bottom of `style.css`.
3. TODO 1: cut the four desktop layout rules out of the top-level CSS.
4. TODO 2: write the phone layout with no media query. Check at 360px.
5. TODO 3: inside `@media (min-width: 600px)`, add the tablet layout. Check at 768px.
6. TODO 4: inside `@media (min-width: 1000px)`, add the laptop layout. Check at 1280px.
7. Drag the device-mode width slowly from 320px to 1400px. Watch the layout change at 600px and 1000px.

## Self-check
- [ ] Outside any `@media` block, no rule creates more than one column.
- [ ] Every media query uses `min-width`, never `max-width`.
- [ ] 360px: one column, no sideways scroll. 768px: two columns. 1280px: three columns.
- [ ] The Limbe card still looks different from the open branches.
