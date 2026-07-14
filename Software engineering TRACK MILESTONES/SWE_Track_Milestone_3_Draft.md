# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 3: Responsive & Mobile-First Design

**Status:** Draft for review
**Unlocks:** After Milestone 2 is fully completed
**Theme:** Designing for every screen — media queries, mobile-first workflow, CSS Grid, responsive images, and making the portfolio page from Milestone 2 truly production-grade across devices.
**Target fellow:** Has a live, styled portfolio page with basic JS interactivity; comfortable with core HTML/CSS and Flexbox.

---

### Activity 3.1 — Why Responsive Design Matters
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.4
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=x4u1yp3Msao, https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design
- **Prompt:** Fellow explores how the same website looks/behaves on phone vs. tablet vs. desktop (using browser dev tools to resize/simulate devices), then writes a short reflection on what breaks or looks awkward on a non-desktop-first site, and why mobile-first matters given how many users in Cameroon/Africa primarily browse on mobile data.
- **Rubric:**
  1. *Observation Quality* (60%) — Did they identify real, specific issues (overflow, tiny text, broken layout)?
  2. *Reasoning* (40%) — Do they connect this to real-world context (mobile-first usage patterns)?

---

### Activity 3.2 — Media Queries & Breakpoints
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.1 | **Prerequisites:** Activity 3.1
- **Evidence Required:** URL/Link (GitHub gist/repo)
- **Resources:** https://www.youtube.com/watch?v=x4u1yp3Msao, https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_media_queries/Using_media_queries
- **Prompt:** Learn `@media` queries and common breakpoints (mobile / tablet / desktop). Build a small standalone demo page with a 3-column layout that collapses to 1 column on mobile and 2 on tablet, using a mobile-first approach (base styles for mobile, `min-width` queries scaling up).
- **Rubric:**
  1. *Mobile-First Approach* (50%) — Are base styles written for mobile first, with `min-width` queries layering up (not the reverse)?
  2. *Correct Behavior* (50%) — Does the layout actually collapse/expand correctly at the right breakpoints?

---

### Activity 3.3 — CSS Grid Fundamentals
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.4
- **Evidence Required:** URL/Link (GitHub gist/repo)
- **Resources:** https://www.youtube.com/watch?v=ieTHC78giGQ, https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_grid_layout/Basic_concepts_of_grid_layout
- **Prompt:** Learn CSS Grid (grid-template-columns/rows, gap, grid-area) as a complement to Flexbox. Build a small responsive image/card gallery (at least 6 items) using Grid, that reflows from 1 column (mobile) to 2 (tablet) to 3–4 (desktop).
- **Rubric:**
  1. *Grid Technique* (60%) — Is Grid used correctly (not Flexbox pretending to be Grid)?
  2. *Responsiveness* (40%) — Does the column count correctly adapt across breakpoints?

---

### Activity 3.4 — Responsive Images & Typography
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.2
- **Evidence Required:** URL/Link (GitHub gist/repo)
- **Resources:** https://www.youtube.com/watch?v=ieTHC78giGQ, https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
- **Prompt:** Learn fluid images (`max-width: 100%`), `srcset` basics, and fluid typography (relative units — `rem`/`em`/`%`/`clamp()` — instead of fixed `px` everywhere). Fellow refactors a sample page's images and text so nothing overflows or becomes unreadable at any screen width.
- **Rubric:**
  1. *No Overflow/Breakage* (50%) — Does the page hold together at all common widths (320px–1920px)?
  2. *Correct Technique* (50%) — Are relative units and fluid image rules actually used, not just hardcoded fixes per breakpoint?

---

### Activity 3.5 — Problem Solving 103: Layout Debugging
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 3.3, Activity 3.4
- **Evidence Required:** Text Response + URL/Link (before/after CodePen or repo)
- **Resources:** https://www.youtube.com/watch?v=x4u1yp3Msao, https://developer.chrome.com/docs/devtools/
- **Prompt:** Fellow is given (or picks) a deliberately broken responsive layout — overlapping elements, horizontal scroll on mobile, unreadable text — and must diagnose and fix it using dev tools, then write a short explanation of what was wrong and how they fixed it.
- **Rubric:**
  1. *Correct Diagnosis* (40%) — Did they correctly identify the root cause(s)?
  2. *Effective Fix* (40%) — Is the layout actually fixed across breakpoints?
  3. *Explanation Clarity* (20%) — Is the reasoning clearly written for a reviewer to follow?

---

### Activity 3.6 — Project: Portfolio Page v2 (Fully Responsive)
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 2.6 | **Prerequisites:** Activity 3.2, Activity 3.3, Activity 3.4
- **Evidence Required:** URL/Link (deployed site)
- **Resources:** https://www.youtube.com/watch?v=ieTHC78giGQ, https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design
- **Prompt:** Take the Milestone 2 portfolio page and rebuild it mobile-first: proper breakpoints, at least one Grid-based section (e.g., a projects/skills gallery), fluid images and typography, and no horizontal scroll or broken layout at any width from 320px to 1920px. Redeploy live.
- **Rubric:**
  1. *Mobile-First Execution* (35%) — Is it genuinely built mobile-first, not just "made to look okay" on mobile after the fact?
  2. *Cross-Device Quality* (35%) — Does it look intentional and functional on phone, tablet, and desktop?
  3. *Technique Variety* (30%) — Does it demonstrate Grid, Flexbox, media queries, and fluid units together, sensibly?

---

### Activity 3.7 — Brand: Before & After
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 3.6 | **Prerequisites:** Activity 3.6
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup with a side-by-side (screenshot or GIF) of the portfolio page on mobile vs. desktop, explaining what "mobile-first" means and why it matters. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine explanation, not just a link/screenshot dump.
  2. *Technical Accuracy* (50%) — Is the explanation of mobile-first actually correct?

---

### Activity 3.8 — Mock Interview: Responsive Design Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.5, Activity 3.6
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Explain mobile-first vs. desktop-first, when to use Flexbox vs. Grid, what a breakpoint is, plus a walkthrough of how they debugged Activity 3.5's broken layout.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 3 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 3.1 | Why Responsive Design Matters | learning | Beginner | 5 | Required |
| 3.2 | Media Queries & Breakpoints | learning | Beginner | 10 | Required |
| 3.3 | CSS Grid Fundamentals | learning | Intermediate | 10 | Required |
| 3.4 | Responsive Images & Typography | learning | Beginner | 10 | Required |
| 3.5 | Problem Solving 103: Layout Debugging | learning | Intermediate | 15 | Required |
| 3.6 | Portfolio Page v2 (Fully Responsive) | project | Intermediate | 20 | Required |
| 3.7 | Before & After (Brand) | blog_post | Beginner | 5 | Optional |
| 3.8 | Mock Interview: Responsive Design Check | mock_interview | Intermediate | 10 | Required |

**Milestone 3 total (if all completed):** 85 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–3)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- **Cumulative possible so far:** 245 pts

---

## Note on freeCodeCamp Alignment
Since you're currently working through freeCodeCamp's Responsive Web Design curriculum yourself, this Milestone's sequencing (media queries → Grid → fluid images/typography → applied project) closely mirrors that path. If there are specific exercises or explanations from that curriculum you've found particularly effective, worth flagging so we can fold that framing directly into Activities 3.2–3.4's instructional content when this moves from outline to full lesson material.

---

## What's Next: Milestone 4 Preview
With a fully responsive portfolio live, the natural next step is **"Talking to a Server"** — HTTP requests/responses, fetching real data from a public API in the browser (rendering it into the page using what they learned about arrays/objects), and understanding client vs. server before writing any backend code themselves. This sets up the jump into actual backend development in Milestone 5. Let me know if you want to proceed there, or redirect again.
