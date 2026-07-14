# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 7: Reconnaissance & Footprinting

**Status:** Draft for review
**Unlocks:** After Milestone 6 is fully completed (Foundations Arc complete)
**Theme:** The first phase of any real security assessment — gathering information about a target before touching it. OSINT (passive recon) and light active recon, entirely within authorized lab environments. This opens the Applied Foundations arc.
**Target fellow:** Ethics-gated; has completed all six Foundations Milestones (networking, Linux, core concepts, cryptography, web fundamentals).

> **Reminder embedded in this Milestone:** every technique here is taught strictly for use against systems fellows own, control, or have explicit written authorization to test (lab platforms, designated ranges). Activity 7.1 reinforces this before any tool is opened.

---

### Activity 7.1 — Recon Scope & Authorization Refresher
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 1 day | **Grace Period:** 1 day | **Late Penalty:** N/A (gate-style, must pass to proceed)
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Ethics Gate)
- **Evidence Required:** Text Response
- **Prompt:** A short, mandatory refresher tied specifically to recon: even passive OSINT against a real company/person without authorization can cross ethical and sometimes legal lines (e.g., aggressive scraping, harassment-adjacent behavior). Fellow answers 3 scenario questions distinguishing acceptable OSINT practice (e.g., researching a company's own public job postings for a sanctioned engagement) from unacceptable (e.g., digging into a private individual's personal life "for practice").
- **Rubric:**
  1. *Scenario Accuracy* (100%) — All 3 scenarios must be correctly identified to proceed (pass/fail gate for this Milestone).

---

### Activity 7.2 — OSINT Fundamentals
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 7.1 | **Prerequisites:** Activity 7.1
- **Evidence Required:** Text Response
- **Prompt:** Learn what Open Source Intelligence (OSINT) is and its legitimate uses (pentesting recon, threat intel, due diligence, journalism). Fellow lists 6+ categories of publicly available information a company unintentionally exposes (job postings revealing tech stack, employee LinkedIn profiles, DNS records, exposed subdomains, social media, public code repos) and explains why each is useful to an attacker or a defender assessing exposure.
- **Rubric:**
  1. *Category Completeness* (60%) — Are a good range of OSINT categories identified?
  2. *Reasoning Quality* (40%) — Is the "why it matters" explanation sound for each?

---

### Activity 7.3 — Hands-On: OSINT on a Designated Target
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.2 | **Prerequisites:** Activity 7.2
- **Evidence Required:** File Upload (report document)
- **Prompt:** Using only a **designated practice target** (a platform-provided fictional company profile, or a TryHackMe OSINT room's built-in scenario — never a real company or individual), fellow performs OSINT gathering and compiles a short recon report: discovered subdomains (via `crt.sh` or similar), any exposed employee info in the scenario, technologies identified, and a summary of what an attacker could do with these findings.
- **Rubric:**
  1. *Thoroughness* (40%) — Is a reasonable range of information gathered from the designated target?
  2. *Report Quality* (30%) — Is it organized like a real recon report a client could read?
  3. *Risk Summary* (30%) — Is the "what an attacker could do with this" analysis sound?

---

### Activity 7.4 — DNS & Subdomain Enumeration
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 7.2
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Learn subdomain enumeration techniques (certificate transparency logs via `crt.sh`, DNS brute-forcing concepts with tools like `sublist3r` or `amass`) against an authorized lab target only (TryHackMe/lab-provided domains, never real domains without explicit permission). Fellow screenshots discovered subdomains and explains why an unused/forgotten subdomain (e.g., an old staging site) is a common real-world attack entry point.
- **Rubric:**
  1. *Correct Tool Usage* (50%) — Are enumeration tools correctly used against the authorized target?
  2. *Risk Understanding* (50%) — Is the "forgotten subdomain" risk correctly explained?

---

### Activity 7.5 — Whois, Metadata & Passive Fingerprinting
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 7.4
- **Evidence Required:** File Upload (terminal screenshots) + Text Response
- **Prompt:** Learn `whois` lookups and document metadata analysis (e.g., PDF/image metadata sometimes reveals author names, software versions, internal file paths — using `exiftool`). Fellow performs a `whois` lookup on an authorized lab domain and analyzes metadata on a sample provided document, listing what unintended information it reveals.
- **Rubric:**
  1. *Correct Tool Usage* (50%) — Are `whois`/`exiftool` used correctly?
  2. *Information Analysis* (50%) — Is the unintentionally revealed information correctly identified and explained?

---

### Activity 7.6 — Problem Solving: Build a Recon Plan
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 7.4, Activity 7.5
- **Evidence Required:** Text Response
- **Prompt:** Given a short written engagement brief (e.g., "you've been authorized to perform recon on ExampleCorp ahead of a pentest — scope: their public web presence only, no social engineering of employees"), fellow plans out their recon methodology step by step: what they'd check first, what tools they'd use for each step, and how they'd stay within the defined scope (explicitly flagging what would be out of bounds).
- **Rubric:**
  1. *Methodology Soundness* (50%) — Is the planned approach logical and reasonably complete?
  2. *Scope Discipline* (50%) — Does the plan correctly respect and explicitly flag scope boundaries?

---

### Activity 7.7 — Hands-On: Recon Lab Room
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.3 | **Prerequisites:** Activity 7.6
- **Evidence Required:** Text Response (flags) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner recon-focused TryHackMe room (e.g., an "OSINT" or "Passive Reconnaissance" path room) applying the techniques from this Milestone in a fully guided, authorized context. Submit flags and a short reflection.
- **Rubric:**
  1. *Correct Completion* (70%) — Flags correctly submitted with proof?
  2. *Reflection Quality* (30%) — Genuine insight shown?

---

### Activity 7.8 — Brand: Anatomy of a Recon Report
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 7.3 | **Prerequisites:** Activity 7.3, Activity 7.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video walking through the recon report from Activity 7.3 (using only the designated fictional/lab target, never a real company) — what was found, methodology used, and why it matters to a real organization's security posture.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Scope Discipline* (15%) — Confirms use of only authorized/designated targets, no real-world targeting?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 7.9 — Mock Interview: Recon & OSINT Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.6, Activity 7.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain the difference between passive and active recon, walk through their recon plan from Activity 7.6, discuss scope discipline, and explain why forgotten subdomains/exposed metadata matter.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 7 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 7.1 | Recon Scope & Authorization Refresher ⚠️ | learning | Beginner | 5 | Required — Gate |
| 7.2 | OSINT Fundamentals | learning | Beginner | 10 | Required |
| 7.3 | Hands-On: OSINT on a Designated Target | project | Intermediate | 20 | Required |
| 7.4 | DNS & Subdomain Enumeration | learning | Intermediate | 15 | Required |
| 7.5 | Whois, Metadata & Passive Fingerprinting | learning | Intermediate | 15 | Required |
| 7.6 | Problem Solving: Build a Recon Plan | learning | Intermediate | 15 | Required |
| 7.7 | Hands-On: Recon Lab Room | project | Intermediate | 15 | Required |
| 7.8 | Anatomy of a Recon Report (Brand) | blog_post | Beginner | 10 | Required |
| 7.9 | Mock Interview: Recon & OSINT Check | mock_interview | Intermediate | 10 | Required |

**Milestone 7 total (if all completed):** 115 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–7)
- Milestones 1–6: 650 pts
- Milestone 7: 115 pts
- **Cumulative possible so far:** 765 pts

---

## Operational Note
Activity 7.3 and 7.8 both depend on having a **"designated fictional/lab target"** — worth deciding whether this is a TryHackMe scenario room (simplest, already built) or a custom I-NNOVA-KICKSTARTER-built fictional company profile fellows practice against repeatedly across Milestones 7–9. A custom fictional company (with a name, fake "employees," fake domain structure hosted on a lab range) would actually make for a great recurring thread across the whole Applied Foundations arc — worth considering as a platform investment.

---

## What's Next: Milestone 8 Preview
**Vulnerability Scanning & Assessment** — Nmap fundamentals (port/service scanning), interpreting scan results, and an intro to vulnerability scanners (e.g., Nessus Essentials/OpenVAS free tiers), moving from "gathering information" to "identifying weaknesses." Say the word when ready.
