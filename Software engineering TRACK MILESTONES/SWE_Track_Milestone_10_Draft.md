# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 10: Capstone — Full-Stack Application

**Status:** Draft for review
**Unlocks:** After Milestone 9 is fully completed
**Theme:** The consolidation checkpoint. Fellows plan, design, build, secure, test, and deploy one complete full-stack application, drawing on everything from Milestones 1–9 (responsive UI, DOM interactivity, API consumption, Express + MySQL backend, Git collaboration, and authentication/RBAC). This is the portfolio centerpiece of the "vanilla JS full-stack" arc, before frameworks are introduced.
**Target fellow:** Has completed the full solo-developer arc — can build, style, secure, and persist a full-stack app independently.

---

### Suggested Capstone Project Options
To keep this grounded without requiring a single mandated idea, fellows choose (or propose their own, admin-approved) one of these scenario types, deliberately close to real problems I-NNOVA CM solves for clients:
1. **Mini Inventory/POS System** — products, categories, stock levels, simple sales log (echoes POSINNOVA).
2. **Mini School Management Tool** — students, classes, attendance or grades (echoes EduTrustISMS/ScholarTrack).
3. **Community Listings/Marketplace** — user-posted listings with categories and search (echoes GoMarket/Cameroon Community).
4. **Fellow's own proposed idea** — must be approved by a mentor/admin against the same scope requirements before starting.

---

### Activity 10.1 — Capstone Planning: Requirements & Scope
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.7
- **Evidence Required:** Text Response (or File Upload if submitted as a doc)
- **Resources:** https://www.youtube.com/watch?v=7UlslIXHNsw, https://www.atlassian.com/agile/product-management/minimum-viable-product
- **Prompt:** Fellow selects a capstone project option (or proposes their own) and writes a short scope document: core features (must-have, MVP-level), stretch features (nice-to-have), the two+ user roles involved, and out-of-scope items they're deliberately not building. This mirrors real client scoping work.
- **Rubric:**
  1. *Scope Clarity* (50%) — Is the MVP clearly and realistically defined (not too ambitious, not trivial)?
  2. *Role Definition* (30%) — Are user roles and their permissions clearly identified?
  3. *Realism* (20%) — Is the scope achievable within a capstone timeframe?

---

### Activity 10.2 — System Design: Architecture, Schema & Wireframes
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.1 | **Prerequisites:** Activity 10.1
- **Evidence Required:** File Upload (ER diagram + wireframes, PDF)
- **Resources:** https://www.youtube.com/watch?v=HXV3zeQKqGY, https://dbdiagram.io/home
- **Prompt:** Design the full database schema (ER diagram, at least 3–4 related tables) and low-fidelity wireframes for the key screens (can be hand-drawn/photographed, Figma, or even plain boxes-and-labels — polish isn't the point, clarity is). Also list the planned API routes (method + path + purpose).
- **Rubric:**
  1. *Schema Quality* (40%) — Correct relationships, sensible normalization, matches the scope from 10.1?
  2. *Wireframe Clarity* (30%) — Do wireframes clearly represent the planned UI and user flow?
  3. *API Route Plan* (30%) — Is the planned route list complete and RESTful?

---

### Activity 10.3 — Backend Build: Core API
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 8 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.2 | **Prerequisites:** Activity 10.2
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://www.freecodecamp.org/news/rest-api-design-best-practices-build-a-rest-api/
- **Prompt:** Build the Express + MySQL backend per the Activity 10.2 design: full CRUD across all planned tables, authentication (signup/login/logout), role-based route protection, input validation, and parameterized queries throughout.
- **Rubric:**
  1. *Functional Completeness* (35%) — Are all planned routes implemented and working?
  2. *Auth & RBAC* (25%) — Is authentication and role-based access correctly enforced?
  3. *Data Integrity* (20%) — Correct relationships, validation, parameterized queries?
  4. *Code Organization* (20%) — Is the codebase reasonably organized (routes/controllers separated, not one giant file)?

---

### Activity 10.4 — Frontend Build: Responsive, Interactive UI
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 8 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.2 | **Prerequisites:** Activity 10.2
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=5fb2aPlgoys, https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design
- **Prompt:** Build the frontend per the Activity 10.2 wireframes using HTML/CSS/vanilla JS: fully responsive (mobile-first), DOM-driven interactivity, and structured to consume the Activity 10.3 API (can be built in parallel against a mock/expected API shape, then wired together in Activity 10.5).
- **Rubric:**
  1. *Responsiveness* (30%) — Works correctly across mobile/tablet/desktop, matching Milestone 3 standards?
  2. *Interactivity* (30%) — Meaningful DOM-driven interactivity (forms, dynamic rendering, state handling)?
  3. *Design Quality* (20%) — Presentable, coherent visual design matching the wireframes?
  4. *Code Organization* (20%) — Reasonably modular JS, not one giant script?

---

### Activity 10.5 — Integration & Testing
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 10.3, Activity 10.4
- **Evidence Required:** URL/Link (GitHub repo) + Text Response (test log)
- **Resources:** https://www.youtube.com/watch?v=OFpqvaJ3QYg, https://learning.postman.com/docs/introduction/overview/
- **Prompt:** Connect frontend to the real backend end-to-end. Fellow manually tests every core user flow (signup, login, each CRUD operation, role restrictions) and keeps a short test log noting what was tested, what broke, and how it was fixed.
- **Rubric:**
  1. *Working Integration* (50%) — Does the full app work end-to-end with no broken core flows?
  2. *Testing Rigor* (30%) — Does the test log show genuine, thorough testing (not just "it works")?
  3. *Bug Resolution* (20%) — Were discovered issues actually fixed, not just noted?

---

### Activity 10.6 — Git Workflow: Capstone Version Control
- **Type:** `project`
- **Pillar:** Collaborate
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** Runs alongside 10.3–10.5 (no separate deadline; evaluated at submission)
- **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 10.2
- **Evidence Required:** URL/Link (GitHub repo commit/branch history)
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests
- **Prompt:** Throughout the build, fellow uses proper Git practices from Milestone 8: feature branches (not everything on `main`), meaningful commit messages, and at least 3 self-reviewed PRs merged into `main` over the course of the capstone (even working solo, PRs create a reviewable history).
- **Rubric:**
  1. *Branch Discipline* (40%) — Is work done on feature branches, not directly on `main`?
  2. *Commit Quality* (30%) — Are commit messages clear and meaningfully scoped (not one giant commit)?
  3. *PR History* (30%) — Are there at least 3 real PRs with descriptions in the merge history?

---

### Activity 10.7 — Deployment: Ship It Live
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 10.5 | **Prerequisites:** Activity 10.5
- **Evidence Required:** URL/Link (live deployed app)
- **Resources:** https://www.youtube.com/watch?v=IVfM_tpFlUc, https://render.com/docs
- **Prompt:** Deploy both frontend and backend to live, publicly accessible URLs (e.g., Render/Railway for the API + MySQL, Netlify/Vercel or same host for the frontend), with environment variables properly configured (no secrets committed to the repo).
- **Rubric:**
  1. *Live & Functional* (60%) — Is the deployed app actually working end-to-end, not just "deployed but broken"?
  2. *Secure Configuration* (40%) — Are secrets/env vars handled correctly, nothing sensitive committed to Git?

---

### Activity 10.8 — Problem Solving 110: Capstone Debugging Log
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 10.7
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://developer.mozilla.org/en-US/docs/Learn/Common_questions/Tools_and_setup/What_are_browser_developer_tools
- **Prompt:** Looking back across the whole capstone, fellow documents the 2–3 hardest bugs or design problems they hit, how they diagnosed the root cause, and what they'd do differently next time. This builds the habit of reflective debugging, a core professional skill.
- **Rubric:**
  1. *Depth of Reflection* (60%) — Are the chosen problems genuinely non-trivial, with real diagnostic reasoning shown?
  2. *Actionable Learning* (40%) — Is there a clear, specific takeaway for next time (not generic "I learned to debug better")?

---

### Activity 10.9 — Brand: Capstone Showcase
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 10.7 | **Prerequisites:** Activity 10.7
- **Evidence Required:** URL/Link (LinkedIn post & YouTube video)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://columncontent.com/linkedin-post-ideas-developers/
- **Prompt:** Post a proper capstone showcase on LinkedIn: what the app does, who it's for, a short demo, tech stack used, and a link to the live app and repo. **Video Requirement:** Record a (3-5 minute) YouTube video where you present your screen, demonstrate the app's functionality, and then dive into the code to explain your architecture decisions, database schema, and how you solved the hardest bug you encountered. Include this YouTube link in your LinkedIn post. This is the most portfolio-visible post so far — treat it as a real project launch announcement.
- **Rubric:**
  1. *Presentation Quality* (40%) — Is it polished and demo-driven, not just a text description?
  2. *Completeness* (30%) — Does it cover what the app does, the stack, and include working links?
  3. *Authenticity* (30%) — Does it read as a genuine, personal project launch rather than generic copy?

---

### Activity 10.10 — Capstone Defense (Human Mock Interview)
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 10.8, Activity 10.9
- **Interview Mode:** Human Interview (first Human-mode interview in the track — fellow schedules with a mentor)
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Fellow walks a mentor through the live app: architecture decisions, schema design, auth/RBAC implementation, the hardest bug from 10.8, and answers follow-up questions probing genuine understanding (not memorized explanations). This is the closest simulation yet of a real technical interview/project defense.
- **Rubric:** Mentor-scored against passing score, using a structured rubric covering technical depth, ability to explain decisions, and handling of follow-up questions (to be finalized with your mentor team).

---

## Milestone 10 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 10.1 | Capstone Planning: Requirements & Scope | learning | Intermediate | 10 | Required |
| 10.2 | System Design: Architecture, Schema & Wireframes | project | Advanced | 15 | Required |
| 10.3 | Backend Build: Core API | project | Advanced | 30 | Required |
| 10.4 | Frontend Build: Responsive, Interactive UI | project | Advanced | 30 | Required |
| 10.5 | Integration & Testing | project | Advanced | 20 | Required |
| 10.6 | Git Workflow: Capstone Version Control | project | Intermediate | 10 | Required |
| 10.7 | Deployment: Ship It Live | project | Intermediate | 15 | Required |
| 10.8 | Problem Solving 110: Capstone Debugging Log | learning | Advanced | 10 | Required |
| 10.9 | Capstone Showcase (Brand) | blog_post | Intermediate | 10 | Required (not optional — this is the portfolio centerpiece) |
| 10.10 | Capstone Defense (Human Mock Interview) | mock_interview | Advanced | 20 | Required |

**Milestone 10 total (if all completed):** 170 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–10)
- Milestones 1–9: 865 pts
- Milestone 10: 170 pts
- **Cumulative possible so far:** 1,035 pts

---

## Notes / Decisions Needed
1. **10.9 is marked Required, not Optional** — unlike the Brand activities in earlier Milestones, this is the flagship portfolio piece of the whole foundational arc, so I'd recommend not letting fellows skip it. Confirm you agree.
2. **10.10 needs real mentor bandwidth** — this is the first Human Interview mode used in the track. Worth confirming mentor capacity/scheduling logistics exist before this goes live, since it can't be purely AI-automated like prior mock interviews.
3. **Deployment costs** — Activities 10.3/10.7 assume free-tier hosting (Render/Railway/Netlify/Vercel) is sufficient. Worth double-checking these still have viable free tiers at build time, since platforms change pricing.

---

## What's Next: Milestone 11 Preview
With the capstone complete, fellows have a full, deployed, portfolio-ready application and are due for a well-earned checkpoint before Milestone 11 introduces **React** — replacing raw DOM manipulation with component-based frontend architecture, and setting up the eventual contrast with Livewire used in your own Cameroon Community platform. Say the word when ready to continue.
