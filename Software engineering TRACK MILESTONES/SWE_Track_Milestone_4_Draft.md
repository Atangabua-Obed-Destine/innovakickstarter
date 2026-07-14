# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 4: Bringing Pages to Life with JavaScript (DOM & Events)

**Status:** Draft for review
**Unlocks:** After Milestone 3 is fully completed
**Theme:** DOM manipulation, event handling, and interactivity — turning static responsive pages into dynamic ones. This is the missing bridge before Milestone 5 (talking to a server), since fetched data needs somewhere to go.
**Target fellow:** Has a fully responsive portfolio site; comfortable with JS functions, arrays, objects, and core HTML/CSS.

---

### Activity 4.1 — Meet the DOM
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.2
- **Evidence Required:** URL/Link (GitHub gist)
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://developer.mozilla.org/en-US/docs/Web/API/Document_Object_Model/Introduction
- **Prompt:** Learn what the DOM is (the live tree representation of a page) and core selection methods (`getElementById`, `querySelector`, `querySelectorAll`). Fellow writes a script that selects several elements on a sample page and logs their content/attributes to the console.
- **Rubric:**
  1. *Correct Selection* (60%) — Are the right elements selected using appropriate methods?
  2. *Understanding* (40%) — Does console output show correct, expected values (not guesswork)?

---

### Activity 4.2 — Changing the Page: Content & Styles
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.1 | **Prerequisites:** Activity 4.1
- **Evidence Required:** URL/Link (GitHub gist/repo)
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://www.freecodecamp.org/news/the-javascript-dom-manipulation-handbook/
- **Prompt:** Learn `.textContent`/`.innerHTML`, `.classList` (add/remove/toggle), and `.style`. Fellow builds a small demo: a button that toggles a "dark mode" class on the page, and another element whose text changes when clicked.
- **Rubric:**
  1. *Functionality* (60%) — Do the toggles/changes work correctly and reliably?
  2. *Good Practice* (40%) — Is `classList` used for style changes (not excessive inline `.style` manipulation)?

---

### Activity 4.3 — Event Handling
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.2 | **Prerequisites:** Activity 4.2
- **Evidence Required:** URL/Link (GitHub gist/repo)
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
- **Prompt:** Learn `addEventListener` and common events (`click`, `input`, `submit`, `keydown`). Fellow builds a small live character counter: a text box that shows "X characters remaining" as the user types, turning red when over a limit.
- **Rubric:**
  1. *Correctness* (60%) — Does the counter update accurately and in real time?
  2. *Edge Case Handling* (40%) — Does the red/warning state trigger correctly at the limit?

---

### Activity 4.4 — Building & Removing Elements Dynamically
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.3 | **Prerequisites:** Activity 4.3
- **Evidence Required:** URL/Link (deployed demo or GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://developer.mozilla.org/en-US/docs/Web/API/Document/createElement
- **Prompt:** Learn `createElement`, `appendChild`/`append`, and `remove()`. Fellow builds a simple to-do list: an input + "Add" button that creates new list items dynamically, each with a "Delete" button that removes just that item.
- **Rubric:**
  1. *Add Functionality* (40%) — Can items be added correctly and repeatedly?
  2. *Delete Functionality* (40%) — Does delete remove only the correct, targeted item?
  3. *Code Quality* (20%) — Is the DOM manipulation reasonably clean (no leftover bugs, no memory of "ghost" elements)?

---

### Activity 4.5 — Problem Solving 104: Interactive Logic
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 4.4
- **Evidence Required:** URL/Link (GitHub repo) + Text Response
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://www.freecodecamp.org/news/the-javascript-dom-manipulation-handbook/
- **Prompt:** Fellow extends the to-do list from 4.4 with slightly harder logic: mark items as "complete" (strikethrough on click), filter buttons ("All / Active / Completed"), and a live counter of remaining items. Written explanation of how state (which items are complete) is tracked.
- **Rubric:**
  1. *Correct Behavior* (50%) — Do complete-toggling and filtering work correctly together?
  2. *State Management Reasoning* (30%) — Is their explanation of how they tracked "state" clear and correct?
  3. *Code Quality* (20%) — Clean, non-duplicated logic?

---

### Activity 4.6 — Project: Interactive Portfolio Feature
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.6 | **Prerequisites:** Activity 4.4, Activity 4.5
- **Evidence Required:** URL/Link (deployed site)
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://www.freecodecamp.org/news/how-to-manipulate-the-dom-beginners-guide/
- **Prompt:** Add one genuinely useful interactive feature to their live portfolio site (from Milestone 3) using DOM manipulation — e.g., a dynamically filterable projects/skills section, a contact form with live validation feedback, or a dark/light mode toggle that persists visually across the page. Must involve dynamic DOM changes, not just CSS `:hover`.
- **Rubric:**
  1. *Functionality* (40%) — Does the feature work correctly and reliably?
  2. *User Experience* (30%) — Does it feel polished and intuitive, not janky?
  3. *Integration Quality* (30%) — Does it fit naturally into the existing responsive design from Milestone 3 (no breakage)?

---

### Activity 4.7 — Brand: Feature Walkthrough
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 4.6 | **Prerequisites:** Activity 4.6
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup/demo (screenshot or short screen-recording GIF) showing off the new interactive feature and briefly explaining how it works under the hood. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine explanation, not just a link drop.
  2. *Technical Accuracy* (50%) — Correctly describes what the feature does and how.

---

### Activity 4.8 — Mock Interview: DOM & Interactivity Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.5, Activity 4.6
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Explain what the DOM is, difference between `textContent` and `innerHTML`, how event listeners work, and a walkthrough of how they managed "state" in the to-do list / portfolio feature.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 4 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 4.1 | Meet the DOM | learning | Beginner | 10 | Required |
| 4.2 | Changing the Page: Content & Styles | learning | Beginner | 10 | Required |
| 4.3 | Event Handling | learning | Beginner | 10 | Required |
| 4.4 | Building & Removing Elements Dynamically | learning | Intermediate | 15 | Required |
| 4.5 | Problem Solving 104: Interactive Logic | learning | Intermediate | 15 | Required |
| 4.6 | Interactive Portfolio Feature | project | Intermediate | 20 | Required |
| 4.7 | Feature Walkthrough (Brand) | blog_post | Beginner | 5 | Optional |
| 4.8 | Mock Interview: DOM & Interactivity Check | mock_interview | Intermediate | 10 | Required |

**Milestone 4 total (if all completed):** 95 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–4)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- Milestone 4: 95 pts
- **Cumulative possible so far:** 340 pts

---

## What's Next: Milestone 5 Preview
Now that fellows can manipulate the DOM and handle events, Milestone 5 — **"Talking to a Server"** — can proceed as originally planned: HTTP requests/responses, `fetch()`, consuming a public API, and rendering real fetched data into the DOM using everything from Milestone 4. This is the natural bridge into backend concepts (Milestone 6+), where they'll eventually build their own API instead of just consuming one. Say the word when you want it drafted.
