# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 8: Vulnerability Scanning & Assessment

**Status:** Draft for review
**Unlocks:** After Milestone 7 is fully completed
**Theme:** Moving from "gathering information" to "identifying weaknesses" — Nmap fundamentals, interpreting scan results, and an intro to vulnerability scanners. All scanning performed strictly against authorized lab targets.
**Target fellow:** Ethics-gated; has completed the Foundations Arc plus Reconnaissance & Footprinting.

---

### Activity 8.1 — Scanning Scope & Authorization Refresher
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 1 day | **Grace Period:** 1 day | **Late Penalty:** N/A (gate-style, must pass to proceed)
- **Chain Parent:** None | **Prerequisites:** Activity 7.1
- **Evidence Required:** Text Response
- **Prompt:** Active scanning is a step up from passive recon — it directly touches target systems, and unauthorized scanning is illegal in most jurisdictions even without exploitation. Fellow answers 3 scenario questions confirming they understand scanning is only ever performed against systems they own or are explicitly authorized to test (lab ranges, designated platforms), and that scanning real, non-lab infrastructure without permission — even "just to see" — is a hard boundary.
- **Rubric:**
  1. *Scenario Accuracy* (100%) — All 3 scenarios must be correctly identified to proceed (pass/fail gate for this Milestone).

---

### Activity 8.2 — Nmap Fundamentals
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 8.1 | **Prerequisites:** Activity 8.1
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Learn Nmap basics: host discovery, TCP connect vs. SYN scans, common flags (`-sV` for service version, `-p` for port ranges, `-A` for aggressive scan). Fellow runs 3 different scan types against an authorized lab target (e.g., a TryHackMe machine or a deliberately vulnerable VM like Metasploitable2 run locally) and screenshots the results, briefly explaining what each scan type revealed differently.
- **Rubric:**
  1. *Correct Tool Usage* (50%) — Are scans correctly run against an authorized target?
  2. *Interpretation* (50%) — Are differences between scan types correctly explained?

---

### Activity 8.3 — Interpreting Scan Results
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.2 | **Prerequisites:** Activity 8.2
- **Evidence Required:** Text Response
- **Prompt:** Given a sample Nmap output (provided, or their own from 8.2), fellow identifies each open port's likely service, flags anything that looks outdated or risky (e.g., an old FTP version with known vulnerabilities, based on the banner shown), and prioritizes findings by likely severity — connecting back to the ports/protocols knowledge from Milestone 2.
- **Rubric:**
  1. *Service Identification* (40%) — Are ports/services correctly identified from the output?
  2. *Risk Flagging* (40%) — Are risky findings correctly flagged with sound reasoning?
  3. *Prioritization* (20%) — Is the severity ranking reasonable?

---

### Activity 8.4 — Intro to Vulnerability Scanners
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 8.2
- **Evidence Required:** Text Response
- **Prompt:** Learn what dedicated vulnerability scanners (Nessus Essentials free tier, OpenVAS/Greenbone) do beyond Nmap — matching discovered services/versions against known CVE databases, CVSS scoring. Fellow explains the difference between Nmap (discovery) and a vulnerability scanner (assessment against known-vulnerability databases), and looks up one real CVE on the NVD (National Vulnerability Database), summarizing its CVSS score and what it means in plain language.
- **Rubric:**
  1. *Conceptual Accuracy* (50%) — Is the Nmap vs. vulnerability scanner distinction correctly explained?
  2. *CVE Analysis* (50%) — Is the researched CVE and its CVSS score correctly summarized?

---

### Activity 8.5 — Hands-On: Vulnerability Scan
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.4 | **Prerequisites:** Activity 8.4
- **Evidence Required:** File Upload (scan report/screenshots)
- **Prompt:** Install and run OpenVAS/Greenbone (or Nessus Essentials) against an authorized lab target (Metasploitable2 is ideal here — it's deliberately vulnerable and designed for this). Fellow exports/screenshots the scan results and picks the 3 highest-severity findings to summarize in plain language.
- **Rubric:**
  1. *Correct Execution* (50%) — Is the scanner correctly configured and run against the authorized target?
  2. *Findings Summary* (50%) — Are the top findings correctly identified and clearly explained?

---

### Activity 8.6 — Problem Solving: Prioritizing a Vulnerability Report
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.5 | **Prerequisites:** Activity 8.5
- **Evidence Required:** Text Response
- **Prompt:** Given a list of 8 fictional scan findings (varying CVSS scores, varying business context — e.g., "critical vuln on a rarely-used internal test server" vs. "medium vuln on the public-facing payment page"), fellow prioritizes them for remediation, explaining that raw CVSS score alone isn't the whole story — business context (exposure, data sensitivity, exploitability in practice) matters too. This is a genuinely important, often-overlooked real-world skill.
- **Rubric:**
  1. *Prioritization Soundness* (50%) — Is the final ranking well-reasoned, not just sorted by CVSS score?
  2. *Business Context Reasoning* (50%) — Is the "score isn't everything" reasoning correctly applied?

---

### Activity 8.7 — Hands-On: Scanning Lab Room
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 8.3, Activity 8.5
- **Evidence Required:** Text Response (flags) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner scanning-focused TryHackMe room (e.g., their "Nmap" path room) reinforcing scan techniques and result interpretation in a fully guided context. Submit flags and a short reflection.
- **Rubric:**
  1. *Correct Completion* (70%) — Flags correctly submitted with proof?
  2. *Reflection Quality* (30%) — Genuine insight shown?

---

### Activity 8.8 — Brand: Reading Between the Ports
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 8.5 | **Prerequisites:** Activity 8.5, Activity 8.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video walking through the vulnerability scan from Activity 8.5 (against only the authorized lab target) — what was found, how it was prioritized, and what remediation would look like. This is exactly the kind of writeup real junior pentesters build a public track record on.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Scope Discipline* (15%) — Confirms authorized lab target only?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 8.9 — Mock Interview: Scanning & Assessment Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 8.6, Activity 8.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain Nmap scan types, the difference between a port scanner and a vulnerability scanner, walk through their prioritization reasoning from Activity 8.6, and discuss CVSS scoring's limitations.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 8 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 8.1 | Scanning Scope & Authorization Refresher ⚠️ | learning | Beginner | 5 | Required — Gate |
| 8.2 | Nmap Fundamentals | learning | Beginner | 15 | Required |
| 8.3 | Interpreting Scan Results | learning | Intermediate | 15 | Required |
| 8.4 | Intro to Vulnerability Scanners | learning | Intermediate | 15 | Required |
| 8.5 | Hands-On: Vulnerability Scan | project | Intermediate | 20 | Required |
| 8.6 | Problem Solving: Prioritizing a Vulnerability Report | learning | Advanced | 20 | Required |
| 8.7 | Hands-On: Scanning Lab Room | project | Intermediate | 15 | Required |
| 8.8 | Reading Between the Ports (Brand) | blog_post | Beginner | 10 | Required |
| 8.9 | Mock Interview: Scanning & Assessment Check | mock_interview | Intermediate | 10 | Required |

**Milestone 8 total (if all completed):** 125 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–8)
- Milestones 1–7: 765 pts
- Milestone 8: 125 pts
- **Cumulative possible so far:** 890 pts

---

## Operational Note
Activities 8.2, 8.5, and 8.7 assume access to a deliberately vulnerable practice VM (Metasploitable2 is the free, standard choice) alongside TryHackMe. Worth confirming fellows can run this locally (resource requirements are modest, but worth flagging as a setup step — possibly folded into Milestone 1 or 3's toolkit setup as a "you'll need this later" note) or whether it should be hosted centrally for consistency.

---

## What's Next: Milestone 9 Preview
**Web Application Security Basics (OWASP Top 10 Intro)** — moving from network-level scanning into the most common real-world vulnerability category: web app flaws. This will use PortSwigger's Web Security Academy labs heavily (free, excellent, purpose-built for exactly this). Say the word when ready.
