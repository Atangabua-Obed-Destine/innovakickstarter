# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 9: Authentication & Authorization

**Status:** Draft for review
**Unlocks:** After Milestone 8 is fully completed
**Theme:** Securely handling user accounts — password hashing, sessions vs. JWTs, protected routes, and role-based access. Directly relevant to the platform's own Rookie/Intern/Professional/Elite role model and to real client systems like EduTrust Pay.
**Target fellow:** Has a persistent MySQL-backed REST API from Milestone 7; has never built login/signup functionality.

---

### Activity 9.1 — Why You Never Store Plain-Text Passwords
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.7
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=6FOq4cUdH8k, https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html
- **Prompt:** Fellow researches real-world data breach consequences of storing plain-text passwords, and learns the basic concept of password hashing (one-way, can't be reversed) versus encryption (reversible). Write a short explanation of why hashing (not encryption) is the correct approach for passwords, and what "salting" adds.
- **Rubric:**
  1. *Conceptual Accuracy* (70%) — Correctly distinguishes hashing from encryption, and explains salting?
  2. *Real-World Awareness* (30%) — Shows understanding of why this matters (breach consequences)?

---

### Activity 9.2 — Building Signup with Hashed Passwords
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.1
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=6FOq4cUdH8k, https://www.npmjs.com/package/bcrypt
- **Prompt:** Add a `users` table (with a hashed password column, never plain text) to the Milestone 7 database, and build a `POST /signup` route using `bcrypt` (or similar) to hash passwords before storing, with basic validation (email format, minimum password length, duplicate email rejection).
- **Rubric:**
  1. *Correct Hashing* (50%) — Are passwords properly hashed before storage, never stored in plain text?
  2. *Validation* (30%) — Are invalid signups (bad email, weak password, duplicate) correctly rejected?
  3. *Code Quality* (20%) — Clean route structure, sensible error responses?

---

### Activity 9.3 — Building Login & Sessions
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.2 | **Prerequisites:** Activity 9.2
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=6FOq4cUdH8k, https://www.npmjs.com/package/express-session
- **Prompt:** Learn how login works: comparing a submitted password against the stored hash (`bcrypt.compare`), and the concept of sessions (server remembers you're logged in via a cookie) vs. tokens. Build a `POST /login` route that verifies credentials and starts a session (using `express-session` or similar), plus a `POST /logout` route.
- **Rubric:**
  1. *Correct Verification* (50%) — Does login correctly accept valid credentials and reject invalid ones?
  2. *Session Handling* (50%) — Is the session correctly created on login and destroyed on logout?

---

### Activity 9.4 — Sessions vs. JWTs
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.3
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=xrj3zzaqODw, https://www.loginradius.com/blog/engineering/guest-post/jwt-vs-sessions
- **Prompt:** Learn the conceptual difference between session-based auth (server-side state, cookie) and token-based auth using JWTs (stateless, token carries the data, common for APIs consumed by mobile apps or separate frontends). Fellow writes a comparison covering tradeoffs (scalability, revocation difficulty for JWTs, statelessness) and identifies which approach would fit a scenario like a mobile app calling a backend API (relevant to I-NNOVA CM's own mobile work).
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Correct understanding of the tradeoffs between the two approaches?
  2. *Applied Reasoning* (40%) — Correctly reasons about which fits the given mobile-app scenario and why?

---

### Activity 9.5 — Protecting Routes with Middleware
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.3 | **Prerequisites:** Activity 9.3, Activity 9.4
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://expressjs.com/en/guide/using-middleware.html
- **Prompt:** Write custom Express middleware that checks if a user is logged in (valid session) before allowing access to a protected route (e.g., `GET /profile`), returning 401 Unauthorized if not. Test both the success and failure paths.
- **Rubric:**
  1. *Correct Protection* (60%) — Does the middleware correctly block unauthenticated requests and allow authenticated ones?
  2. *Proper Status Codes* (40%) — Is 401 (not authenticated) used correctly, distinct from other error types?

---

### Activity 9.6 — Problem Solving 109: Role-Based Access Control
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.5 | **Prerequisites:** Activity 9.5
- **Evidence Required:** URL/Link (GitHub repo) + Text Response
- **Resources:** https://www.youtube.com/watch?v=HHuiV841g_w, https://permify.co/post/role-based-access-control-rbac-nodejs-expressjs/
- **Prompt:** Extend the `users` table with a `role` column (e.g., `fellow`, `mentor`, `admin` — echoing the platform's own tiered fellow system) and build middleware that restricts certain routes to specific roles (e.g., only `admin` can `DELETE` a user). Fellow writes a short explanation of how this design would extend to something like restricting grading actions to mentors/admins only.
- **Rubric:**
  1. *Correct RBAC Implementation* (50%) — Does role-based restriction work correctly across roles?
  2. *Design Reasoning* (30%) — Is the explanation of extending this to a real grading-permissions scenario sound?
  3. *Code Quality* (20%) — Clean, reusable middleware design?

---

### Activity 9.7 — Project: Secure Multi-Role API
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 6 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.6 | **Prerequisites:** Activity 9.6
- **Evidence Required:** URL/Link (GitHub repo, with README describing auth flow and roles)
- **Resources:** https://www.youtube.com/watch?v=HHuiV841g_w, https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html
- **Prompt:** Bring together everything from Milestones 7–9: a fully persistent REST API with signup, login/logout, hashed passwords, protected routes, and at least two distinct roles with different permissions on at least 3 routes. Document the auth flow and role permissions clearly in the README.
- **Rubric:**
  1. *Full Auth Flow* (35%) — Signup, login, logout, and protected routes all work correctly end-to-end?
  2. *Role-Based Permissions* (30%) — Are role restrictions correctly enforced across multiple routes?
  3. *Security Practices* (20%) — Hashed passwords, no plain-text leaks, sensible session/token handling?
  4. *Documentation* (15%) — Clear README explaining the auth flow and roles?

---

### Activity 9.8 — Brand: Building Secure Systems
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 9.7 | **Prerequisites:** Activity 9.7
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup on building their first authentication system — what they learned about password security, sessions vs. tokens, and role-based access. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine explanation, not just a link drop.
  2. *Technical Accuracy* (50%) — Correctly describes auth concepts covered in this Milestone.

---

### Activity 9.9 — Mock Interview: Auth & Security Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.6, Activity 9.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain hashing vs. encryption, sessions vs. JWTs, how their protected-route middleware works, and how role-based access control was implemented in their project.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 9 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 9.1 | Why You Never Store Plain-Text Passwords | learning | Beginner | 5 | Required |
| 9.2 | Building Signup with Hashed Passwords | project | Beginner | 15 | Required |
| 9.3 | Building Login & Sessions | learning | Intermediate | 15 | Required |
| 9.4 | Sessions vs. JWTs | learning | Intermediate | 10 | Required |
| 9.5 | Protecting Routes with Middleware | learning | Intermediate | 15 | Required |
| 9.6 | Problem Solving 109: Role-Based Access Control | learning | Advanced | 15 | Required |
| 9.7 | Secure Multi-Role API | project | Advanced | 25 | Required |
| 9.8 | Building Secure Systems (Brand) | blog_post | Beginner | 5 | Optional |
| 9.9 | Mock Interview: Auth & Security Check | mock_interview | Advanced | 15 | Required |

**Milestone 9 total (if all completed):** 120 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–9)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- Milestone 4: 95 pts
- Milestone 5: 90 pts
- Milestone 6: 105 pts
- Milestone 7: 120 pts
- Milestone 8: 90 pts
- Milestone 9: 120 pts
- **Cumulative possible so far:** 865 pts

---

## What's Next
This closes out the foundational "solo full-stack developer" arc — fellows can now build, style, make interactive, connect to a live API, persist data, collaborate via Git, and secure an application end-to-end. A natural checkpoint before Milestone 10.

Two directions from here, worth your input:
1. **Frontend Frameworks** (React is the dominant industry choice, and would let fellows finally replace raw DOM manipulation with a modern approach — also relevant since your Cameroon Community platform uses Livewire, a different paradigm worth contrasting later).
2. **A larger Capstone Milestone** consolidating everything so far into one substantial full-stack project (e.g., a simplified version of something like POSINNOVA or a mini school management tool) before introducing new frameworks — giving fellows a strong portfolio piece and a natural "vanilla JS mastery" checkpoint.

Let me know which direction, or if you want to reorder differently.
