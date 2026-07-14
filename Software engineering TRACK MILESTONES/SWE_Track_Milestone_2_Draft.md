# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 2: Building Blocks of the Web

**Status:** Draft for review
**Unlocks:** After Milestone 1 is fully completed
**Theme:** JavaScript fundamentals (functions, arrays, objects), HTML/CSS basics, harder problem-solving, first mini "real" web page.
**Target fellow:** Has completed orientation, knows basic logic/variables/loops, made first Git commits and first JS program.

---

### Activity 2.1 — Functions & Reusability
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.6
- **Evidence Required:** URL/Link (GitHub gist)
- **Resources:** https://www.youtube.com/watch?v=jS4aFq5-91M, https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Functions
- **Prompt:** Learn what functions are and why they exist (avoiding repetition, organizing logic). Write 3 small JavaScript functions (e.g., a function to check if a number is even, one to find the largest of three numbers, one to reverse a string) and push to a gist or repo.
- **Rubric:**
  1. *Correctness* (60%) — Do all 3 functions work as intended across test inputs?
  2. *Reusability* (40%) — Are functions written generically (parameters, not hardcoded values) rather than one-off scripts?

---

### Activity 2.2 — Arrays & Objects
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.1 | **Prerequisites:** Activity 2.1
- **Evidence Required:** URL/Link (GitHub gist)
- **Resources:** https://www.youtube.com/watch?v=jS4aFq5-91M, https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Indexed_collections
- **Prompt:** Learn arrays (loops over data, `.push`, `.map`/`.filter` conceptually introduced) and objects (key-value structure). Fellow writes a small script that stores a list of "fellow profiles" (name, role, points) as an array of objects, then loops through and prints a formatted summary of each.
- **Rubric:**
  1. *Data Structure Use* (60%) — Are arrays and objects used correctly and sensibly?
  2. *Output Correctness* (40%) — Does the loop correctly process and print every entry?

---

### Activity 2.3 — HTML Fundamentals: Structure of the Web
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.5
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=pQN-pnXPaVg, https://developer.mozilla.org/en-US/docs/Learn/HTML/Introduction_to_HTML
- **Prompt:** Learn core HTML tags (headings, paragraphs, lists, links, images, semantic tags like `header`/`main`/`footer`). Build a bare (unstyled) single-page personal profile: name, short bio, a list of interests, a link to their GitHub.
  > *Recommended Resource:* Work through the first few modules of the **freeCodeCamp Responsive Web Design** certification to build your HTML foundation.
- **Rubric:**
  1. *Semantic Structure* (50%) — Are appropriate tags used (not everything wrapped in `<div>`)?
  2. *Completeness* (50%) — Are all required sections present and valid HTML?

---

### Activity 2.4 — CSS Fundamentals: Styling Your Page
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.3 | **Prerequisites:** Activity 2.3
- **Evidence Required:** URL/Link (deployed site or repo with screenshot)
- **Resources:** https://www.youtube.com/watch?v=ieTHC78giGQ, https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_flexible_box_layout/Basic_concepts_of_flexbox
- **Prompt:** Learn selectors, box model, colors/typography, and basic Flexbox. Style the Activity 2.3 profile page: layout, colors, spacing, at least one Flexbox-based section (e.g., interests as a row of cards).
  > *Recommended Resource:* freeCodeCamp's **Basic CSS** and **CSS Flexbox** modules will give you exactly the tools you need to pass this activity.
- **Rubric:**
  1. *Visual Quality* (50%) — Does it look intentional and reasonably polished (not default browser styling)?
  2. *Technique* (50%) — Is Flexbox actually used correctly for at least one layout section?

---

### Activity 2.5 — Problem Solving 102: Working With Data
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 2.2
- **Evidence Required:** URL/Link (GitHub gist)
- **Resources:** https://www.youtube.com/watch?v=jS4aFq5-91M, https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array
- **Prompt:** Slightly harder than Milestone 1's problem-solving set — 3–4 small array/object manipulation challenges (e.g., "find duplicates in a list," "count how many fellows scored above X," "group items by category"). Fellow solves in JavaScript and writes a short comment above each explaining their approach before the code.
- **Rubric:**
  1. *Correct Solutions* (50%) — Do all challenges produce correct results?
  2. *Approach Explanation* (30%) — Is the reasoning written clearly before the code?
  3. *Code Quality* (20%) — Reasonably clean, no obviously wasteful repetition?

---

### Activity 2.6 — Mini Project: Personal Portfolio Page v1
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 2.4 | **Prerequisites:** Activity 2.4, Activity 2.1
- **Evidence Required:** URL/Link (deployed site, e.g., GitHub Pages/Netlify)
- **Resources:** https://www.youtube.com/watch?v=b2r9Cdvssi0, https://docs.github.com/en/pages/quickstart
- **Prompt:** Combine everything so far — extend the styled profile page (2.4) with at least one small piece of JS interactivity (e.g., a button that toggles a "show more about me" section, or dynamically renders the fellow's interests list from a JS array instead of hardcoded HTML). Deploy it live (GitHub Pages is fine).
- **Rubric:**
  1. *Functionality* (40%) — Does the JS interactivity work correctly?
  2. *Design Quality* (30%) — Does the page look complete and presentable — this is portfolio-facing.
  3. *Deployment* (30%) — Is it actually live at a public URL?

---

### Activity 2.7 — Brand: Show Off Your Portfolio Page
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 2.6 | **Prerequisites:** Activity 2.6
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post the live portfolio link on LinkedIn with a short writeup of what they learned building it (HTML/CSS/JS basics). Tag/hashtag as in Milestone 1.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine reflection, not just a link drop.
  2. *Presentation* (50%) — Is the live link included and working?

---

### Activity 2.8 — Mock Interview: Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.5, Activity 2.6
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100 (slightly higher bar than Milestone 1)
- **Required Sessions:** 1
- **Focus:** Basic JS concept questions (what's a function, what's an array/object, difference between them), a walk-through of how they built the portfolio page, plus one very light logic question live.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 2 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 2.1 | Functions & Reusability | learning | Beginner | 10 | Required |
| 2.2 | Arrays & Objects | learning | Beginner | 10 | Required |
| 2.3 | HTML Fundamentals | project | Beginner | 10 | Required |
| 2.4 | CSS Fundamentals | project | Beginner | 10 | Required |
| 2.5 | Problem Solving 102 | learning | Intermediate | 15 | Required |
| 2.6 | Mini Project: Portfolio Page v1 | project | Intermediate | 20 | Required |
| 2.7 | Show Off Your Portfolio Page | blog_post | Beginner | 5 | Optional |
| 2.8 | Mock Interview: Fundamentals Check | mock_interview | Beginner | 10 | Required |

**Milestone 2 total (if all completed):** 90 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–2)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- **Cumulative possible so far:** 160 pts

Under the normalization model, a fellow who's completed both Milestones fully sits at 100% of *currently published* content — as Milestone 3+ gets added, that denominator grows and their percentage adjusts down until they complete more.

---

## What's Next: Milestone 3 Preview
Natural next step is **"Talking to a Server"** — introducing what a backend actually is, HTTP requests/responses, fetching data from a public API in the browser, and probably the first taste of Node.js or a lightweight backend concept, alongside a harder problem-solving set and portfolio page v2 (now fetching live data). Let me know if that direction is right, or if you'd rather insert a dedicated **"Responsive Web Design Deep Dive"** Milestone before going backend — where we could mandate fellows actually complete the freeCodeCamp Responsive Web Design Certification and submit their verified public certificate URL for points!
