# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 6: Web Fundamentals for Security

**Status:** Draft for review
**Unlocks:** After Milestone 5 is fully completed
**Theme:** A security-focused pass at how web applications actually work — requests/responses, cookies, sessions, and client vs. server trust boundaries. This is the last foundations Milestone before the hands-on Applied Foundations arc (Recon, Scanning, OWASP Top 10) begins.
**Target fellow:** Ethics-gated; comfortable with networking, Linux, core security concepts, and cryptography basics. No coding background required — this is security-focused, not development-focused.

> **Note:** Fellows with HTML/JavaScript familiarity (e.g., from the Software Engineering track) will find this Milestone easier, since reading and reasoning about web page structure helps here — but it's explicitly **not a hard prerequisite**. Everything is taught from a security-analysis angle, not a "build a website" angle.

---

### Activity 6.1 — How a Web Request Actually Works
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.3
- **Evidence Required:** File Upload (annotated screenshot)
- **Prompt:** Learn the request/response cycle from a security lens: what a browser sends (method, headers, sometimes a body) and what a server sends back (status code, headers, body). Fellow opens their browser's Developer Tools (Network tab), loads a simple page, and screenshots one real request with headers visible, annotating what each key header does (e.g., `User-Agent`, `Cookie`, `Content-Type`).
- **Rubric:**
  1. *Correct Capture* (50%) — Is a real request correctly captured with visible headers?
  2. *Header Understanding* (50%) — Are the annotated headers correctly explained?

---

### Activity 6.2 — Cookies & Sessions
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.1 | **Prerequisites:** Activity 6.1
- **Evidence Required:** File Upload (screenshot) + Text Response
- **Prompt:** Learn how cookies work (name/value pairs, `Secure`, `HttpOnly`, `SameSite` flags) and how sessions use them to keep a user "logged in." Fellow logs into a website they have an account on, inspects the session cookie in DevTools, and explains what would happen — and why it's dangerous — if that cookie's value were stolen by an attacker (session hijacking, conceptually explained, no exploitation performed).
- **Rubric:**
  1. *Correct Inspection* (40%) — Is a real session cookie correctly located and its flags identified?
  2. *Risk Explanation* (60%) — Is the session hijacking risk correctly and safely explained at a conceptual level?

---

### Activity 6.3 — Client-Side vs. Server-Side Trust
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.1
- **Evidence Required:** Text Response
- **Prompt:** Learn the single most important web security principle: **never trust the client**. Anything happening in the browser (JavaScript validation, hidden form fields, disabled buttons) can be bypassed by an attacker, so real security enforcement must happen server-side. Fellow explains this principle with a concrete example (e.g., "a price field is validated only in JavaScript") and describes what could go wrong if a developer relied on client-side validation alone.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the client/server trust principle correctly explained?
  2. *Applied Example* (40%) — Is the example concrete and the risk correctly reasoned through?

---

### Activity 6.4 — Reading a Web Page's Structure (Security Lens)
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.1
- **Evidence Required:** File Upload (annotated screenshot)
- **Prompt:** Just enough HTML/form literacy to be dangerous (in the good sense): learn to recognize forms, input fields, hidden fields, and basic page structure using "View Source" and DevTools — not to build pages, but to read them for security-relevant clues (e.g., a hidden field revealing an internal ID, a comment left in the code by a developer). Fellow inspects a real page's source and identifies 2–3 pieces of information a security reviewer might find interesting.
- **Rubric:**
  1. *Correct Inspection* (60%) — Are real, relevant elements correctly identified in the page source?
  2. *Security Relevance* (40%) — Is the "why this matters to a reviewer" reasoning sound?

---

### Activity 6.5 — Intro to Burp Suite
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.2 | **Prerequisites:** Activity 6.2, Activity 6.4
- **Evidence Required:** File Upload (screenshots)
- **Prompt:** Install Burp Suite Community Edition (already available in Kali), configure the browser proxy to route through it, and intercept a real request in an authorized lab environment (e.g., PortSwigger's own practice site, which is explicitly built for this). Fellow screenshots an intercepted request in Burp's Proxy tab and one request sent to Repeater, with a short explanation of what each tool does.
- **Rubric:**
  1. *Correct Setup* (50%) — Is Burp correctly proxying and intercepting traffic?
  2. *Tool Understanding* (50%) — Is the purpose of Proxy vs. Repeater correctly explained?

---

### Activity 6.6 — Problem Solving: Spot the Vulnerability (Conceptual)
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.3, Activity 6.5
- **Evidence Required:** Text Response
- **Prompt:** Given 4–5 short, described (not executable) web app scenarios (e.g., "a login form directly inserts the username into a SQL query string," "a comment field displays user input directly on the page without sanitization," "a URL parameter `?user_id=5` returns a different user's data when changed to `?user_id=6`"), fellow identifies the vulnerability class each represents (at a naming/conceptual level — SQL injection, XSS, IDOR) and explains, without writing exploit payloads, *why* each is dangerous and what the general fix category is (parameterized queries, output encoding, proper authorization checks).
- **Rubric:**
  1. *Correct Classification* (50%) — Are vulnerability types correctly identified?
  2. *Impact & Fix Reasoning* (50%) — Is the danger and general fix direction correctly explained?

---

### Activity 6.7 — Hands-On: Web Fundamentals Lab
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.5 | **Prerequisites:** Activity 6.5
- **Evidence Required:** Text Response (flags) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner web-security-focused TryHackMe room covering HTTP fundamentals from a security angle (e.g., their "Web Fundamentals" path room). Submit flags and a short reflection connecting it back to the Burp Suite setup from 6.5.
- **Rubric:**
  1. *Correct Completion* (70%) — Are flags correctly submitted with proof?
  2. *Reflection Quality* (30%) — Genuine connection made to prior Burp Suite work?

---

### Activity 6.8 — Brand: Web Security 101
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.7 | **Prerequisites:** Activity 6.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video explaining "never trust the client" or another concept from this Milestone in plain language — ideally with a relatable example (e.g., why a store shouldn't rely only on the app to check a price is correct).
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Accessibility* (15%) — Genuinely understandable to a non-technical audience?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 6.9 — Mock Interview: Web Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.6, Activity 6.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain the client/server trust principle, how cookies/sessions work, what Burp Suite is used for, and a walkthrough of their vulnerability classification reasoning from Activity 6.6.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 6 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 6.1 | How a Web Request Actually Works | learning | Beginner | 10 | Required |
| 6.2 | Cookies & Sessions | learning | Beginner | 15 | Required |
| 6.3 | Client-Side vs. Server-Side Trust | learning | Beginner | 15 | Required |
| 6.4 | Reading a Web Page's Structure (Security Lens) | learning | Beginner | 10 | Required |
| 6.5 | Intro to Burp Suite | project | Intermediate | 20 | Required |
| 6.6 | Problem Solving: Spot the Vulnerability (Conceptual) | learning | Intermediate | 20 | Required |
| 6.7 | Hands-On: Web Fundamentals Lab | project | Intermediate | 15 | Required |
| 6.8 | Web Security 101 (Brand) | blog_post | Beginner | 10 | Required |
| 6.9 | Mock Interview: Web Fundamentals Check | mock_interview | Intermediate | 10 | Required |

**Milestone 6 total (if all completed):** 125 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–6)
- Milestone 1: 75 pts
- Milestone 2: 105 pts
- Milestone 3: 110 pts
- Milestone 4: 115 pts
- Milestone 5: 120 pts
- Milestone 6: 125 pts
- **Cumulative possible so far:** 650 pts

---

## This Closes the "Foundations Arc"
Milestones 1–6 form the complete conceptual + basic-tooling foundation: ethics, networking, Linux, core security theory, cryptography, and web fundamentals. From here, the roadmap moves into the **Applied Foundations arc** (still broad, but now hands-on):

- **Milestone 7:** Reconnaissance & Footprinting (OSINT, passive/active recon)
- **Milestone 8:** Vulnerability Scanning & Assessment (Nmap, vulnerability scanners)
- **Milestone 9:** Web Application Security Basics (OWASP Top 10 intro, PortSwigger labs)
- **Milestone 10:** Blue Team Foundations (log analysis, SIEM concepts, basic incident response)
- **Milestone 11:** Capstone — Foundations Assessment (consolidation checkpoint before specialization branching)

Say the word when ready for Milestone 7, or let me know if you'd like to pause here and review the full Foundations Arc as a whole first.
