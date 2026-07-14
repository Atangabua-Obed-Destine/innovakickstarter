# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 5: Cryptography Basics

**Status:** Draft for review
**Unlocks:** After Milestone 4 is fully completed
**Theme:** Hashing vs. encryption, symmetric vs. asymmetric cryptography, digital certificates, and TLS/HTTPS — the concepts underpinning password security, secure communication, and nearly everything fellows will later assess or defend.
**Target fellow:** Ethics-gated, comfortable with networking/Linux/core security vocabulary; no prior cryptography background assumed.

---

### Activity 5.1 — Hashing vs. Encryption
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.2
- **Evidence Required:** Text Response
- **Prompt:** Learn the core distinction: hashing is one-way (verification), encryption is two-way (confidentiality, reversible with a key). Fellow explains both in their own words, gives one real use case for each (e.g., hashing for password storage, encryption for a private message), and explains why using encryption for password storage instead of hashing would be a mistake.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the hashing/encryption distinction correctly explained?
  2. *Applied Reasoning* (40%) — Is the "why encryption is wrong for passwords" reasoning sound?

---

### Activity 5.2 — Hands-On: Hashing in the Terminal
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.1 | **Prerequisites:** Activity 5.1
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Use `md5sum`, `sha1sum`, and `sha256sum` on a sample text file, then change one character in the file and re-hash it. Fellow screenshots the before/after hashes and writes a short note explaining the "avalanche effect" they observed (small input change → completely different hash).
- **Rubric:**
  1. *Correct Tool Usage* (60%) — Are the hashing commands run correctly?
  2. *Avalanche Effect Understanding* (40%) — Is the observed effect correctly explained?

---

### Activity 5.3 — Symmetric vs. Asymmetric Encryption
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 5.1
- **Evidence Required:** Text Response
- **Prompt:** Learn symmetric encryption (same key encrypts/decrypts, e.g., AES) versus asymmetric encryption (public/private key pairs, e.g., RSA), and the key distribution problem symmetric encryption has that asymmetric solves. Fellow explains both with a real-world analogy of their own (not a copied textbook analogy — e.g., not just "a locked mailbox"), and explains at a conceptual level how the two are often combined in practice (asymmetric to exchange a symmetric session key, then symmetric for the bulk data — as TLS does).
- **Rubric:**
  1. *Conceptual Accuracy* (50%) — Correct distinction and understanding of each?
  2. *Original Analogy* (30%) — Is the analogy genuinely their own and apt?
  3. *Combined Usage Understanding* (20%) — Do they correctly grasp why both are used together in practice?

---

### Activity 5.4 — Hands-On: Generating & Using SSH Keys
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.3 | **Prerequisites:** Activity 5.3
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Generate an SSH key pair (`ssh-keygen`), inspect the public and private key files, and set up key-based authentication to a lab VM or a free-tier cloud instance (instead of password auth). Fellow screenshots the successful key-based login and explains, in a short note, why key-based auth is stronger than password auth here — connecting it back to the asymmetric encryption concept from 5.3.
- **Rubric:**
  1. *Correct Setup* (60%) — Is SSH key auth correctly generated and working?
  2. *Conceptual Link* (40%) — Is the connection to asymmetric crypto correctly explained?

---

### Activity 5.5 — TLS/HTTPS & Certificates
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.3 | **Prerequisites:** Activity 5.3
- **Evidence Required:** File Upload (annotated screenshot)
- **Prompt:** Learn what TLS/HTTPS actually does (encrypts traffic, verifies server identity via certificates issued by a Certificate Authority) and the basic TLS handshake at a conceptual level. Fellow inspects a real website's certificate in their browser (issuer, validity dates, domain match) for 2–3 sites, screenshots the certificate details, and identifies what would look suspicious about a certificate (e.g., self-signed on a major site, expired, domain mismatch).
- **Rubric:**
  1. *Correct Inspection* (50%) — Is the certificate information correctly captured and read?
  2. *Suspicious Indicator Awareness* (50%) — Are red flags correctly identified and explained?

---

### Activity 5.6 — Problem Solving: Password Storage Design
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.2 | **Prerequisites:** Activity 5.2, Activity 5.3
- **Evidence Required:** Text Response
- **Prompt:** Fellow is given a flawed scenario ("a small startup stores user passwords using plain MD5 with no salt") and must diagnose every problem with this approach (fast hashing algorithm vulnerable to rainbow tables, no salting, no key stretching) and propose a correct modern approach (e.g., bcrypt/Argon2 with salting and appropriate work factor), explaining why each fix matters. This directly echoes the correct approach fellows will have already used in the Software Engineering track's Milestone 9 (Authentication), if applicable — good cross-track reinforcement.
- **Rubric:**
  1. *Problem Diagnosis* (40%) — Are all major flaws correctly identified?
  2. *Correct Solution* (40%) — Is the proposed fix modern, correct, and well-justified?
  3. *Explanation Clarity* (20%) — Is the reasoning clearly communicated?

---

### Activity 5.7 — Hands-On: Cryptography Lab Room
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 5.5, Activity 5.6
- **Evidence Required:** Text Response (flags) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner cryptography-focused TryHackMe room (e.g., their "Cryptography Basics" or equivalent path room), covering hashing, encoding vs. encryption, and basic cipher concepts in a guided, hands-on format. Submit flags and a short reflection on the most surprising thing learned.
- **Rubric:**
  1. *Correct Completion* (70%) — Are flags correctly submitted with proof?
  2. *Reflection Quality* (30%) — Genuine insight shown?

---

### Activity 5.8 — Brand: Demystifying Cryptography
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.7 | **Prerequisites:** Activity 5.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video explaining one cryptography concept in plain language — e.g., "Why 'HTTPS' Means Your Data Is Safe (Mostly)" or "Hashing vs. Encryption: What's the Difference?" Aim for genuine accessibility to a non-technical audience.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Accessibility* (15%) — Genuinely understandable to a non-technical audience?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 5.9 — Mock Interview: Cryptography Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.6, Activity 5.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain hashing vs. encryption, symmetric vs. asymmetric, how TLS/HTTPS establishes trust, and a walkthrough of their password storage redesign from Activity 5.6.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 5 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 5.1 | Hashing vs. Encryption | learning | Beginner | 10 | Required |
| 5.2 | Hands-On: Hashing in the Terminal | project | Beginner | 10 | Required |
| 5.3 | Symmetric vs. Asymmetric Encryption | learning | Intermediate | 15 | Required |
| 5.4 | Hands-On: Generating & Using SSH Keys | project | Intermediate | 15 | Required |
| 5.5 | TLS/HTTPS & Certificates | learning | Intermediate | 15 | Required |
| 5.6 | Problem Solving: Password Storage Design | learning | Intermediate | 20 | Required |
| 5.7 | Hands-On: Cryptography Lab Room | project | Intermediate | 15 | Required |
| 5.8 | Demystifying Cryptography (Brand) | blog_post | Beginner | 10 | Required |
| 5.9 | Mock Interview: Cryptography Fundamentals Check | mock_interview | Intermediate | 10 | Required |

**Milestone 5 total (if all completed):** 120 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–5)
- Milestone 1: 75 pts
- Milestone 2: 105 pts
- Milestone 3: 110 pts
- Milestone 4: 115 pts
- Milestone 5: 120 pts
- **Cumulative possible so far:** 525 pts

---

## What's Next: Milestone 6 Preview
**Web Fundamentals for Security** — a security-focused pass at how HTTP/web applications actually work (requests, cookies, sessions, client vs. server) — just enough for fellows to understand *what's being attacked* before the Applied Foundations arc begins (Recon, Scanning, OWASP Top 10). This is also where the optional "SWE-track HTML/JS familiarity helps" recommendation gets flagged for fellows who want it. Say the word when ready.
