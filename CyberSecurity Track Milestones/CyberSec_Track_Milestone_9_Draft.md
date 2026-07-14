# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 9: Web Application Security Basics (OWASP Top 10 Intro)

**Status:** Draft for review
**Unlocks:** After Milestone 8 is fully completed
**Theme:** The most common real-world vulnerability category — web application flaws. Introduces the OWASP Top 10 with hands-on practice exclusively via PortSwigger's Web Security Academy, a free platform purpose-built with legally sanctioned, safe vulnerable labs for exactly this kind of learning.
**Target fellow:** Ethics-gated; has completed Foundations Arc, Recon, and Scanning Milestones.

> **Note on this Milestone's approach:** All hands-on vulnerability exploitation happens exclusively within PortSwigger's own guided lab environment, where PortSwigger provides the vulnerable targets and step-by-step methodology by design. Fellows follow PortSwigger's own official lab walkthroughs for technique — this curriculum focuses on ensuring fellows understand *why* each vulnerability exists, how to *recognize* and *explain* it, and how to communicate it professionally, rather than serving as a standalone exploit-writing reference itself.

---

### Activity 9.1 — OWASP Top 10 Overview
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.6
- **Evidence Required:** Text Response
- **Prompt:** Learn what the OWASP Top 10 is (a community-driven, regularly updated list of the most critical web application security risks) and why it matters as an industry-standard reference. Fellow lists the current Top 10 categories and, for each, writes a one-sentence plain-language description of what it means.
- **Rubric:**
  1. *Accuracy* (70%) — Are current categories and descriptions correct?
  2. *Clarity* (30%) — Are descriptions genuinely plain-language, not copied jargon?

---

### Activity 9.2 — Broken Access Control & IDOR
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.1 | **Prerequisites:** Activity 9.1, Activity 6.5
- **Evidence Required:** Text Response (lab completion confirmation) + File Upload (screenshot of completed lab)
- **Prompt:** Complete 2–3 beginner "Access Control" labs on PortSwigger Web Security Academy (including at least one Insecure Direct Object Reference/IDOR lab), following their official walkthroughs if needed. Fellow writes a short explanation in their own words of what broken access control is, why IDOR specifically happens (missing authorization checks on object references), and the general fix category (server-side authorization checks on every request, never trusting client-supplied IDs alone).
- **Rubric:**
  1. *Lab Completion* (40%) — Are the labs genuinely completed with proof?
  2. *Conceptual Explanation* (40%) — Is the vulnerability class correctly explained in the fellow's own words?
  3. *Fix Understanding* (20%) — Is the general remediation direction correctly understood?

---

### Activity 9.3 — SQL Injection Fundamentals
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 9.1
- **Evidence Required:** Text Response (lab completion confirmation) + File Upload (screenshot of completed lab)
- **Prompt:** Complete 2–3 beginner SQL Injection labs on PortSwigger Web Security Academy. Fellow writes an explanation of why SQL injection happens (untrusted input concatenated directly into a query), connecting it back to the parameterized-query fix they already applied in the Software Engineering track's backend work (if familiar), or simply explaining parameterized queries/prepared statements as the standard fix.
- **Rubric:**
  1. *Lab Completion* (40%) — Are the labs genuinely completed with proof?
  2. *Conceptual Explanation* (40%) — Is the root cause correctly explained?
  3. *Fix Understanding* (20%) — Is parameterization correctly explained as the fix?

---

### Activity 9.4 — Cross-Site Scripting (XSS)
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 9.1
- **Evidence Required:** Text Response (lab completion confirmation) + File Upload (screenshot of completed lab)
- **Prompt:** Complete 2–3 beginner Cross-Site Scripting labs (reflected and stored) on PortSwigger Web Security Academy. Fellow explains the difference between reflected and stored XSS, why unsanitized user input rendered back into a page is dangerous (e.g., session cookie theft, page defacement, phishing pivot), and the general fix category (output encoding/escaping, Content Security Policy).
- **Rubric:**
  1. *Lab Completion* (40%) — Are the labs genuinely completed with proof?
  2. *Reflected vs. Stored Distinction* (30%) — Correctly explained?
  3. *Fix Understanding* (30%) — Is output encoding/CSP correctly explained as mitigation?

---

### Activity 9.5 — Security Misconfiguration & Sensitive Data Exposure
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 9.1
- **Evidence Required:** Text Response
- **Prompt:** Learn common security misconfigurations (default credentials left active, verbose error messages leaking stack traces/internal paths, unnecessary services exposed, missing security headers) and sensitive data exposure risks (unencrypted data in transit/at rest, secrets committed to public repos — a mistake even experienced developers make). Fellow finds one real, publicly documented example of a data breach caused by misconfiguration (using only public reporting) and summarizes what went wrong.
- **Rubric:**
  1. *Conceptual Coverage* (60%) — Are misconfiguration categories correctly understood?
  2. *Case Study Quality* (40%) — Is the real example accurately and appropriately summarized?

---

### Activity 9.6 — Problem Solving: Vulnerability Triage on a Sample App
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 9.2, Activity 9.3, Activity 9.4
- **Evidence Required:** Text Response
- **Prompt:** Given a written description of a fictional web app's behavior (5–6 described symptoms, e.g., "search results reflect the search term directly into the page," "user IDs are sequential and visible in the URL," "login errors say 'invalid password' vs 'user not found' differently"), fellow identifies which OWASP Top 10 category each symptom likely represents and ranks them by severity/exploitability, without writing any actual payloads.
- **Rubric:**
  1. *Correct Classification* (50%) — Are vulnerability types correctly identified from the described symptoms?
  2. *Prioritization* (30%) — Is the severity ranking sound?
  3. *Reasoning Clarity* (20%) — Is the analysis clearly communicated?

---

### Activity 9.7 — Writing a Professional Vulnerability Report
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.6 | **Prerequisites:** Activity 9.6
- **Evidence Required:** File Upload (report document)
- **Prompt:** Learn the standard structure of a professional vulnerability/pentest finding (title, severity, description, steps to reproduce, impact, remediation recommendation). Fellow writes 2–3 formal findings based on labs completed in Activities 9.2–9.4, formatted the way they'd appear in a real client-facing report — this is one of the most valuable, transferable skills in the entire field, since clients pay for clear communication as much as technical discovery.
- **Rubric:**
  1. *Structure & Completeness* (35%) — Does each finding include all standard sections?
  2. *Technical Accuracy* (30%) — Are the findings technically correct?
  3. *Professional Communication* (25%) — Is it written the way a paying client would expect to read it (clear, non-alarmist, actionable)?
  4. *Remediation Quality* (10%) — Are fix recommendations specific and correct?

---

### Activity 9.8 — Brand: My First Web Vulnerabilities
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 9.7 | **Prerequisites:** Activity 9.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video covering one or two of the PortSwigger labs completed this Milestone — what the vulnerability was, the general approach (without reproducing PortSwigger's own proprietary lab content verbatim — describe methodology, don't copy their lab text), and what was learned. This is the closest fellows have come yet to a genuine security research writeup, an important portfolio milestone.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, accurate, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality?
  3. *Originality* (25%) — Own explanation/words, not reproduced lab text?
  4. *Authenticity* (15%) — Genuine voice and understanding shown?

---

### Activity 9.9 — Mock Interview: Web App Security Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.6, Activity 9.7
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100 (rising bar, approaching the Foundations Assessment capstone)
- **Required Sessions:** 1
- **Focus:** Explain 3–4 OWASP Top 10 categories in depth, walk through one of their written vulnerability findings from Activity 9.7, and discuss how they'd communicate a critical finding to a non-technical client.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 9 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 9.1 | OWASP Top 10 Overview | learning | Beginner | 10 | Required |
| 9.2 | Broken Access Control & IDOR | project | Intermediate | 20 | Required |
| 9.3 | SQL Injection Fundamentals | project | Intermediate | 20 | Required |
| 9.4 | Cross-Site Scripting (XSS) | project | Intermediate | 20 | Required |
| 9.5 | Security Misconfiguration & Data Exposure | learning | Intermediate | 15 | Required |
| 9.6 | Problem Solving: Vulnerability Triage on a Sample App | learning | Advanced | 20 | Required |
| 9.7 | Writing a Professional Vulnerability Report | project | Advanced | 25 | Required |
| 9.8 | My First Web Vulnerabilities (Brand) | blog_post | Intermediate | 15 | Required |
| 9.9 | Mock Interview: Web App Security Check | mock_interview | Advanced | 15 | Required |

**Milestone 9 total (if all completed):** 160 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–9)
- Milestones 1–8: 890 pts
- Milestone 9: 160 pts
- **Cumulative possible so far:** 1,050 pts

---

## What's Next: Milestone 10 Preview
**Blue Team Foundations** — log analysis, SIEM concepts, and basic incident response workflow, giving every fellow at least foundational defensive exposure before the specialization branch point. This deliberately balances the offensive-heavy focus of Milestones 7–9 before Milestone 11's consolidation capstone. Say the word when ready.
