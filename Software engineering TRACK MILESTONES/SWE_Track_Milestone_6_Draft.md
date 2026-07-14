# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 6: Your First Backend (Node.js & Express)

**Status:** Draft for review
**Unlocks:** After Milestone 5 is fully completed
**Theme:** Crossing from frontend to backend — setting up a Node.js server, building routes with Express, returning JSON, and understanding what actually happens on "the other end" of every `fetch()` call from Milestone 5.
**Target fellow:** Comfortable consuming APIs and handling client-side data; has never written server-side code before.

---

### Activity 6.1 — What Is a Backend, Really?
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.2
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://www.freecodecamp.org/news/get-started-with-nodejs/
- **Prompt:** Fellow reflects on everything they've built so far (a fetch call in Milestone 5) and reasons through: what server responded to that request? What logic might have run before sending back JSON? Write a short explanation of the role a backend plays (business logic, data access, security) versus what the frontend is responsible for.
- **Rubric:**
  1. *Conceptual Accuracy* (70%) — Correct understanding of frontend/backend responsibilities?
  2. *Connects to Prior Work* (30%) — Do they reference their own Milestone 5 fetch experience?

---

### Activity 6.2 — Node.js Environment Setup
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.1
- **Evidence Required:** File Upload (screenshot)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://nodejs.org/en/learn/getting-started/introduction-to-nodejs
- **Prompt:** Install Node.js and npm, initialize a project with `npm init`, and run a simple `console.log` script from the terminal using `node`. Screenshot showing `node -v`, `npm -v`, and the script output.
- **Rubric:**
  1. *Completeness* (100%) — Are Node/npm installed and a script successfully run?

---

### Activity 6.3 — Your First Server
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.2 | **Prerequisites:** Activity 6.2
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://expressjs.com/en/starter/hello-world.html
- **Prompt:** Install Express, build the smallest possible server (`app.listen`), and create a single `GET /` route that returns "Hello, I-NNOVA KICKSTARTER!" Fellow tests it locally and confirms it works in the browser or via a tool like Postman/Thunder Client.
- **Rubric:**
  1. *Server Runs Correctly* (60%) — Does the server start and respond correctly?
  2. *Code Understanding* (40%) — Is the code structured cleanly, showing they understand what each line does (not copy-pasted blindly)?

---

### Activity 6.4 — Routes & JSON Responses
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.3 | **Prerequisites:** Activity 6.3
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://expressjs.com/en/guide/routing.html
- **Prompt:** Learn route parameters, query strings, and `res.json()`. Fellow builds a small API with 3–4 GET routes serving hardcoded data (e.g., `/fellows`, `/fellows/:id`, `/fellows?tier=rookie`) returning proper JSON.
- **Rubric:**
  1. *Route Correctness* (50%) — Do all routes return correct, well-formed JSON?
  2. *Parameter/Query Handling* (50%) — Are route params and query strings correctly used to filter/select data?

---

### Activity 6.5 — Handling POST Requests & Middleware Basics
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.4 | **Prerequisites:** Activity 6.4
- **Evidence Required:** URL/Link (GitHub repo & YouTube video)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://expressjs.com/en/guide/using-middleware.html
- **Prompt:** Learn `express.json()` middleware and how to handle `POST` requests to add data to an in-memory array (no database yet). Fellow adds a `POST /fellows` route that accepts a JSON body and adds a new fellow to the existing in-memory list, plus basic validation (reject if `name` is missing, return 400). **Video Requirement:** Record a short (2-3 minute) YouTube video sharing your screen, explaining how your POST route works and demonstrating it successfully handling requests and validation via Postman/Thunder Client.
- **Rubric:**
  1. *Correct POST Handling* (50%) — Does the route correctly accept and store new data?
  2. *Validation* (30%) — Is missing/invalid input correctly rejected with an appropriate status code?
  3. *Code Quality* (20%) — Reasonably clean route/middleware structure?

---

### Activity 6.6 — Problem Solving 106: Designing an API Surface
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.5
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=iYM2zFP3Zn0, https://restfulapi.net/http-methods/
- **Prompt:** Before more coding, a design exercise: given a simple scenario (e.g., "design the API for a basic to-do list app" or "design the API for I-NNOVA POS's product catalog"), fellow writes out what routes they'd create (method + path), what each should return, and appropriate status codes — without writing implementation code. This builds the habit of planning an API before building it.
- **Rubric:**
  1. *Completeness* (40%) — Are all necessary CRUD routes identified?
  2. *RESTful Correctness* (40%) — Are methods/paths used according to REST conventions?
  3. *Status Code Awareness* (20%) — Are appropriate status codes assigned to each scenario?

---

### Activity 6.7 — Project: Build a Small REST API
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 25
- **Deadline:** 6 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.5 | **Prerequisites:** Activity 6.5, Activity 6.6
- **Evidence Required:** URL/Link (GitHub repo, with README describing routes)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://expressjs.com/en/guide/routing.html
- **Prompt:** Using the design from Activity 6.6 (or a similar simple domain of their choice — e.g., a to-do list, a book list, a simple product catalog), build a full in-memory REST API in Express with GET, POST, PUT/PATCH, and DELETE routes, proper status codes, and basic validation. No database yet — this is about API design and Express fluency.
- **Rubric:**
  1. *Full CRUD Coverage* (40%) — Are all four operations correctly implemented?
  2. *REST Conventions* (30%) — Correct methods, paths, and status codes throughout?
  3. *Validation & Error Handling* (20%) — Are bad requests handled gracefully?
  4. *Documentation* (10%) — Does the README clearly describe available routes?

---

### Activity 6.8 — Brand: Demystifying APIs
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 6.7 | **Prerequisites:** Activity 6.7
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup on the REST API they built — what it does, what they learned building their first backend, and how it connects to everything they consumed as a "client" in Milestone 5. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine explanation, not just a link drop.
  2. *Technical Accuracy* (50%) — Correctly explains what the API does and how it works.

---

### Activity 6.9 — Mock Interview: Backend Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.6, Activity 6.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100 (bar rising as fellows approach the halfway point of foundational content)
- **Required Sessions:** 1
- **Focus:** Explain what middleware is, difference between route params and query strings, REST conventions, how they validated input, and a walkthrough of their API design decisions from Activity 6.6/6.7.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 6 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 6.1 | What Is a Backend, Really? | learning | Beginner | 5 | Required |
| 6.2 | Node.js Environment Setup | project | Beginner | 5 | Required |
| 6.3 | Your First Server | learning | Beginner | 10 | Required |
| 6.4 | Routes & JSON Responses | learning | Beginner | 15 | Required |
| 6.5 | Handling POST Requests & Middleware Basics | learning | Intermediate | 15 | Required |
| 6.6 | Problem Solving 106: Designing an API Surface | learning | Intermediate | 15 | Required |
| 6.7 | Build a Small REST API | project | Intermediate | 25 | Required |
| 6.8 | Demystifying APIs (Brand) | blog_post | Beginner | 5 | Optional |
| 6.9 | Mock Interview: Backend Fundamentals Check | mock_interview | Intermediate | 10 | Required |

**Milestone 6 total (if all completed):** 105 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–6)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- Milestone 4: 95 pts
- Milestone 5: 90 pts
- Milestone 6: 105 pts
- **Cumulative possible so far:** 535 pts

---

## What's Next: Milestone 7 Preview
The in-memory API in Milestone 6 has an obvious gap — data disappears on restart. Milestone 7 is the natural fit for **Databases: Persisting Real Data** — relational database fundamentals (tables, relationships, normalization — echoing your own EduTrustISMS/ScholarTrack schema work), then connecting Node/Express to a real database (likely PostgreSQL or MySQL, worth confirming since MySQL would align with your existing Laravel/POS stack) to persist the REST API's data permanently. Let me know if you want that next, or want to slot in something else first (e.g., authentication, or Git branching/collaboration workflows, which haven't been covered beyond basic commits).
