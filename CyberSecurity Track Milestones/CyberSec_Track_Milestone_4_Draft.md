# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 4: Core Security Concepts

**Status:** Draft for review
**Unlocks:** After Milestone 3 is fully completed
**Theme:** The shared vocabulary of the entire field — CIA triad, threat/vulnerability/risk, common attack categories at a conceptual level, and the basics of security policy. This is the last purely-conceptual Milestone before Cryptography and the Applied Foundations hands-on arc.
**Target fellow:** Ethics-gated, networking- and Linux-comfortable; has not yet studied formal security theory or attack taxonomy.

---

### Activity 4.1 — The CIA Triad
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Ethics Gate)
- **Evidence Required:** Text Response
- **Prompt:** Learn Confidentiality, Integrity, and Availability as the foundational security model. Fellow analyzes 5 real-world scenarios (e.g., "a hospital's patient database gets ransomware-encrypted," "someone reads private messages without permission," "a DDoS takes down a shopping site during a sale") and correctly identifies which CIA property (or properties) each scenario violates, with reasoning.
- **Rubric:**
  1. *Correct Identification* (60%) — Are CIA properties correctly matched to each scenario?
  2. *Reasoning Quality* (40%) — Is the "why" clearly and correctly explained?

---

### Activity 4.2 — Threats, Vulnerabilities & Risk
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.1 | **Prerequisites:** Activity 4.1
- **Evidence Required:** Text Response
- **Prompt:** Learn the precise distinction between a threat (a potential danger), a vulnerability (a weakness), and risk (likelihood × impact), plus the concept of a threat actor. Fellow writes a short scenario of their own (not copied) that includes an identifiable threat, vulnerability, and resulting risk, correctly labeling each part.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Are the three terms correctly distinguished and applied?
  2. *Originality* (40%) — Is the scenario genuinely their own, not a lightly reworded textbook example?

---

### Activity 4.3 — Malware Taxonomy
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.2
- **Evidence Required:** Text Response
- **Prompt:** Learn the major malware categories (virus, worm, trojan, ransomware, spyware, rootkit) and how they differ (self-replication, disguise, persistence, objective). Fellow builds a comparison table and researches one real, publicly documented malware incident (e.g., WannaCry), summarizing what type it was and its impact — using only publicly available reporting, no operational/technical replication detail.
- **Rubric:**
  1. *Taxonomy Accuracy* (60%) — Are categories correctly distinguished?
  2. *Case Study Quality* (40%) — Is the real-world example accurately summarized at an appropriate conceptual level?

---

### Activity 4.4 — Social Engineering & Phishing
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.2
- **Evidence Required:** File Upload (annotated screenshot) + Text Response
- **Prompt:** Learn social engineering principles (authority, urgency, trust exploitation) and phishing variants (phishing, spear phishing, vishing, smishing). Fellow finds a real (already-received, or a published example from an awareness site) phishing email/message, screenshots it with personal details redacted, and annotates 4–5 red flags that reveal it as a phishing attempt.
- **Rubric:**
  1. *Red Flag Identification* (60%) — Are genuine, correct red flags identified?
  2. *Privacy Handling* (20%) — Are personal details properly redacted?
  3. *Explanation Quality* (20%) — Is the reasoning for each flag clear?

---

### Activity 4.5 — Network Attack Categories (Conceptual)
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 4.3, Activity 4.4
- **Evidence Required:** Text Response
- **Prompt:** Learn, at a conceptual (non-operational) level, what Denial of Service (DoS/DDoS), Man-in-the-Middle (MITM), and brute-force attacks are, why they work, and standard defenses against each (rate limiting, encryption/HSTS, account lockout policies). Fellow explains each in their own words and proposes one specific defensive control for each.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Are the attack types correctly and safely explained (concept + impact, not operational instructions)?
  2. *Defense Quality* (40%) — Are proposed defenses genuinely applicable and correctly reasoned?

---

### Activity 4.6 — Problem Solving: Incident Triage Scenario
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.5 | **Prerequisites:** Activity 4.5
- **Evidence Required:** Text Response
- **Prompt:** Given a realistic written incident scenario (e.g., "an employee reports their laptop is running slowly and popups are appearing; a colleague says they clicked a link in an email yesterday") fellow works through a basic triage process: what CIA properties are at risk, what's the likely threat type, what immediate containment steps would they recommend (e.g., disconnect from network, don't power off if forensics matter, notify IT/security lead), and what they'd want to investigate next.
- **Rubric:**
  1. *Diagnostic Reasoning* (40%) — Is the likely threat type correctly reasoned from the symptoms?
  2. *Triage Steps* (40%) — Are containment/next-step recommendations sound and appropriately prioritized?
  3. *Communication* (20%) — Is this clearly written, as if reporting to a non-technical manager?

---

### Activity 4.7 — Security Policy Basics
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 4.2
- **Evidence Required:** File Upload (document)
- **Prompt:** Learn what security policies are and why organizations need them (acceptable use policy, password policy, incident response policy, as common examples). Fellow drafts a short, realistic Acceptable Use Policy (1 page) for a small organization of their choosing (could be modeled generically on a small school or small business, not naming a real client), covering at minimum: password requirements, device usage rules, and reporting obligations for suspected incidents.
- **Rubric:**
  1. *Completeness* (50%) — Are the required policy elements present and sensible?
  2. *Clarity & Professionalism* (30%) — Is it written the way a real, usable policy document would be?
  3. *Appropriateness* (20%) — Is it realistically scoped for a small organization (not copy-pasted enterprise boilerplate)?

---

### Activity 4.8 — Brand: Explain a Core Security Concept
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.6 | **Prerequisites:** Activity 4.6
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video explaining one concept from this Milestone in plain language for a general audience — e.g., "How to Spot a Phishing Email" or "The CIA Triad Explained Simply." Practical, audience-relevant topics (like phishing awareness) tend to perform especially well and build genuine public value alongside personal brand.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Practical Value* (15%) — Does it give the audience something genuinely useful/actionable?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 4.9 — Mock Interview: Core Concepts Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.6, Activity 4.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain the CIA triad, the threat/vulnerability/risk distinction, walk through their incident triage reasoning from Activity 4.6, and discuss key elements of a good security policy.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 4 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 4.1 | The CIA Triad | learning | Beginner | 10 | Required |
| 4.2 | Threats, Vulnerabilities & Risk | learning | Beginner | 10 | Required |
| 4.3 | Malware Taxonomy | learning | Beginner | 10 | Required |
| 4.4 | Social Engineering & Phishing | learning | Beginner | 15 | Required |
| 4.5 | Network Attack Categories (Conceptual) | learning | Intermediate | 15 | Required |
| 4.6 | Problem Solving: Incident Triage Scenario | learning | Intermediate | 20 | Required |
| 4.7 | Security Policy Basics | learning | Intermediate | 15 | Required |
| 4.8 | Explain a Core Security Concept (Brand) | blog_post | Beginner | 10 | Required |
| 4.9 | Mock Interview: Core Concepts Check | mock_interview | Intermediate | 10 | Required |

**Milestone 4 total (if all completed):** 115 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–4)
- Milestone 1: 75 pts
- Milestone 2: 105 pts
- Milestone 3: 110 pts
- Milestone 4: 115 pts
- **Cumulative possible so far:** 405 pts

---

## What's Next: Milestone 5 Preview
**Cryptography Basics** — hashing vs. encryption (symmetric/asymmetric), digital certificates, and TLS/HTTPS — the concepts underpinning nearly everything fellows will assess or defend later (password security, secure comms, certificate validation in web app testing). Say the word when ready.
