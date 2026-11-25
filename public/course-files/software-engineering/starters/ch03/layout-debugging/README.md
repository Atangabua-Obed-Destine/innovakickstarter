# Problem Solving 103: fix the Bafoussam Centre page

Mr. Kenfack Roland, manager of our newest branch, sent this message:

> "I opened our page on my phone. Everything is tiny, so I zoom in. Then I must scroll sideways to read. The shop photo is cut off. The products run off the right side of the screen. Even on the office laptop, the search box sticks out on the right."

There are **5 bugs**. One is in `index.html`. Four are in `style.css`. Find each one with DevTools, fix it, and explain it.

## Rules
- Find the **root cause** before you change code. "The photo is too big" is a symptom. "The photo has a fixed width of N px and nothing lets it shrink" is a root cause.
- Change as little as possible. Do not rewrite the page.
- Keep a fix log as you go (see below).

## Tools
- **Device mode:** Ctrl+Shift+M in DevTools. Test at 360px, 768px and 1280px.
- **Inspect:** right-click a broken element > Inspect. Read its rules in the Styles tab and its real size in the Computed tab.
- **Overflow finder:** paste this into the Console. It lists every element wider than the screen.

```js
document.querySelectorAll('body *').forEach(el => {
  if (el.getBoundingClientRect().right > document.documentElement.clientWidth) console.log(el);
});
```

- **One-line check:** this should print `true` at 360px when you are done.

```js
document.documentElement.scrollWidth <= document.documentElement.clientWidth
```

## Fix log (copy this table into your submission)

| # | Symptom | Root cause | My fix | How I checked |
|---|---|---|---|---|
| 1 | | | | |

## Self-check
- [ ] The one-line check prints `true` at 360px, 768px and 1280px.
- [ ] The overflow finder prints nothing at 360px.
- [ ] My fix log has 5 rows, each with a root cause, not only a symptom.
- [ ] I changed only what each bug needed.
