# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 5: Talking to a Server

**Status:** Draft for review
**Unlocks:** After Milestone 4 is fully completed
**Theme:** HTTP fundamentals, the client-server model, `fetch()`, consuming public APIs, and rendering real external data into the DOM. This is the bridge from "frontend-only" thinking into backend concepts.
**Target fellow:** Comfortable with DOM manipulation, events, and dynamic element creation from Milestone 4.

---

### Activity 5.1 — How the Web Actually Works
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.1
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=iYM2zFP3Zn0, https://developer.mozilla.org/en-US/docs/Learn/Common_questions/Web_mechanics/How_does_the_Internet_work
- **Prompt:** Learn the client-server model: what happens when you type a URL and hit enter (DNS, request, response, rendering — at a conceptual level, no deep networking). Fellow writes a short explanation in their own words, plus identifies the parts of a URL (protocol, domain, path, query string).
- **Rubric:**
  1. *Conceptual Accuracy* (70%) — Is the request/response cycle explained correctly?
  2. *URL Anatomy* (30%) — Are URL parts correctly identified?

---

### Activity 5.2 — HTTP Methods & Status Codes
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.1 | **Prerequisites:** Activity 5.1
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=iYM2zFP3Zn0, https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
- **Prompt:** Learn the common HTTP methods (GET, POST, PUT/PATCH, DELETE) and what they're used for, plus key status code ranges (2xx success, 4xx client error, 5xx server error) with a few specific codes (200, 201, 400, 401, 404, 500). Fellow matches 6–8 real-world scenarios (e.g., "you tried to load a page that doesn't exist") to the correct method/status code.
- **Rubric:**
  1. *Method Understanding* (50%) — Are methods correctly matched to their purpose?
  2. *Status Code Understanding* (50%) — Are status codes correctly matched to scenarios?

---

### Activity 5.3 — Your First Fetch
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.2 | **Prerequisites:** Activity 5.2
- **Evidence Required:** URL/Link (GitHub gist/repo)
- **Resources:** https://www.youtube.com/watch?v=OFpqvaJ3QYg, https://www.freecodecamp.org/news/javascript-fetch-api-for-beginners/
- **Prompt:** Learn `fetch()`, Promises at a basic level (`.then()`), and `async`/`await`. Fellow writes a script that fetches data from a free public API (e.g., a countries or random-fact API), logs the parsed JSON to the console, and handles a basic error case (network failure/bad URL) with `.catch()` or `try/catch`.
- **Rubric:**
  1. *Correct Fetch Usage* (50%) — Does the fetch correctly retrieve and parse JSON data?
  2. *Error Handling* (50%) — Is a failure case handled gracefully rather than crashing silently?

---

### Activity 5.4 — Rendering Real Data into the DOM
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.3 | **Prerequisites:** Activity 5.3, Activity 4.4
- **Evidence Required:** URL/Link (deployed demo or GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=OFpqvaJ3QYg, https://www.freecodecamp.org/news/javascript-fetch-api-for-beginners/
- **Prompt:** Combine `fetch()` with DOM skills from Milestone 4: build a small page that fetches a list of items from a public API (e.g., countries, jokes, quotes) and dynamically renders each as a styled card on the page — no hardcoded HTML for the data itself.
- **Rubric:**
  1. *Data Rendering* (50%) — Are all fetched items correctly and dynamically rendered?
  2. *Loading/Error States* (30%) — Is there a visible loading indicator and a graceful message if the fetch fails?
  3. *Code Quality* (20%) — Clean separation between fetch logic and DOM-rendering logic?

---

### Activity 5.5 — Problem Solving 105: Working With Real-World Data
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 5.4
- **Evidence Required:** URL/Link (GitHub repo) + Text Response
- **Resources:** https://www.youtube.com/watch?v=OFpqvaJ3QYg, https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
- **Prompt:** Fellow extends Activity 5.4 with search/filter functionality over the fetched data (e.g., a search box that filters the rendered cards in real time by name), plus a short written explanation of how they avoided re-fetching data unnecessarily (working with data already in memory vs. hitting the API again).
- **Rubric:**
  1. *Correct Filtering* (50%) — Does search/filter work accurately against the fetched dataset?
  2. *Efficiency Reasoning* (30%) — Is their explanation of avoiding redundant fetches correct?
  3. *Code Quality* (20%) — Clean, readable implementation?

---

### Activity 5.6 — Project: Live Data Feature for Your Portfolio
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.6 | **Prerequisites:** Activity 5.4, Activity 5.5
- **Evidence Required:** URL/Link (deployed site)
- **Resources:** https://www.youtube.com/watch?v=OFpqvaJ3QYg, https://docs.github.com/en/rest
- **Prompt:** Add a genuinely useful live-data feature to their portfolio site — e.g., a "currently reading/latest GitHub repos" widget pulling from the GitHub public API, a weather widget, or a quote-of-the-day widget. Must include loading and error states, and fit responsively into the existing design.
- **Rubric:**
  1. *Functionality* (40%) — Does the live data correctly load and display?
  2. *Robustness* (30%) — Are loading/error states handled gracefully (no broken UI on failure)?
  3. *Integration Quality* (30%) — Does it fit naturally into the existing responsive design?

---

### Activity 5.7 — Brand: What I Learned Consuming APIs
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 5.6 | **Prerequisites:** Activity 5.6
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup demoing the new live-data feature and briefly explaining, in plain language, what an API is and how their site now "talks" to another server. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine explanation, not just a link drop.
  2. *Technical Accuracy* (50%) — Correctly explains what an API is and how the feature works.

---

### Activity 5.8 — Mock Interview: HTTP & API Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.5, Activity 5.6
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Explain the client-server model, difference between GET and POST, what a status code tells you, how `fetch`/`async`/`await` work, and a walkthrough of how they handled loading/error states in their live-data feature.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 5 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 5.1 | How the Web Actually Works | learning | Beginner | 5 | Required |
| 5.2 | HTTP Methods & Status Codes | learning | Beginner | 10 | Required |
| 5.3 | Your First Fetch | learning | Beginner | 10 | Required |
| 5.4 | Rendering Real Data into the DOM | learning | Intermediate | 15 | Required |
| 5.5 | Problem Solving 105: Working With Real-World Data | learning | Intermediate | 15 | Required |
| 5.6 | Live Data Feature for Your Portfolio | project | Intermediate | 20 | Required |
| 5.7 | What I Learned Consuming APIs (Brand) | blog_post | Beginner | 5 | Optional |
| 5.8 | Mock Interview: HTTP & API Check | mock_interview | Intermediate | 10 | Required |

**Milestone 5 total (if all completed):** 90 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–5)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- Milestone 4: 95 pts
- Milestone 5: 90 pts
- **Cumulative possible so far:** 430 pts

---

## What's Next: Milestone 6 Preview
With fellows now comfortable *consuming* APIs, Milestone 6 is the natural pivot into **backend development proper** — setting up a simple server (Node.js/Express is the common industry-standard teaching choice, though given I-NNOVA CM's own stack is PHP/Laravel, worth deciding whether to teach Node first as a transferable concept-builder and introduce Laravel later, or go straight to PHP/Laravel here). This is a good spot to make that call before I draft further, since it affects several Milestones ahead (databases, auth, and the eventual "build your own API" project).
