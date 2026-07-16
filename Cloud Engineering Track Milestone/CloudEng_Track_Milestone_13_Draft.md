# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 13: Cloud Security & IAM Best Practices

**Status:** Draft for review
**Unlocks:** After Milestone 12 is fully completed
**Theme:** A dedicated security-hardening pass over everything built so far — least privilege revisited in depth, encryption at rest/in transit, and networking (Security Groups, NACLs) revisited specifically through a security lens rather than a "make it work" lens.
**Target fellow:** Has built full infrastructure stacks across compute, storage, networking, containers, and observability; has practiced basic IAM/security hygiene throughout but never had a dedicated security-focused Milestone.

---

### Activity 13.1 — The Shared Responsibility Model
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 12.8
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn AWS's Shared Responsibility Model — AWS secures "the cloud" (physical infrastructure, hypervisor), the customer secures "in the cloud" (data, IAM configuration, network rules, patching the OS on their own instances). Fellow explains this split with a concrete example from their own track experience for each side (e.g., "AWS ensures the physical data center is secure" vs. "I'm responsible for not leaving an S3 bucket public" — a direct callback to Milestone 4).
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the Shared Responsibility Model correctly explained?
  2. *Applied Examples* (40%) — Are the personal examples accurate and well-chosen?

---

### Activity 13.2 — IAM Audit: Finding Your Own Over-Permissioned Access
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 13.1 | **Prerequisites:** Activity 13.1
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn IAM Access Analyzer and the "IAM policy simulator." Fellow audits their own AWS account's IAM users/roles accumulated across the entire track so far, honestly identifies any overly broad permissions (many fellows will likely have used `AdministratorAccess` at some point for convenience while learning — a genuine, common real-world anti-pattern worth confronting directly), and tightens at least one policy to be appropriately scoped, documenting the before/after.
- **Rubric:**
  1. *Honest Audit* (40%) — Is a genuine, thorough self-audit performed (not a token gesture)?
  2. *Correct Tightening* (40%) — Is at least one policy correctly scoped down to least privilege?
  3. *Verification* (20%) — Is it confirmed the tightened policy still allows necessary actions (least privilege, not broken access)?

---

### Activity 13.3 — Encryption at Rest & in Transit
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 13.1
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn encryption at rest (S3 bucket encryption, EBS volume encryption, RDS encryption — often a simple checkbox, frequently skipped) and encryption in transit (TLS/HTTPS, connecting back to the Cybersecurity track's TLS Milestone if a fellow has taken it, or taught fresh here otherwise). Fellow enables encryption on an S3 bucket and an EBS volume, verifies the encryption status in the console, and adds an SSL/TLS certificate (via AWS Certificate Manager, free) to their ALB from Milestone 5 to serve HTTPS instead of HTTP, verifying the padlock in the browser. **Tear down resources when done.**
- **Rubric:**
  1. *Encryption at Rest Correctly Enabled* (40%) — Are S3/EBS encryption correctly demonstrated?
  2. *Encryption in Transit Correctly Enabled* (40%) — Is HTTPS correctly configured and verified on the ALB?
  3. *Cost Discipline* (20%) — Is teardown correctly completed?

---

### Activity 13.4 — Security Groups & NACLs: The Hardening Pass
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 13.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reviews their own Milestone 7 custom VPC's Security Groups with fresh, security-focused eyes: is SSH (port 22) open to `0.0.0.0/0` (a genuinely common real-world misconfiguration and the single most attacked port on the internet) or correctly restricted to a specific IP/range? Fellow documents every rule found, flags any that are overly permissive, and rewrites them to be appropriately scoped — this is a "hardening audit" writeup, not new infrastructure.
- **Rubric:**
  1. *Thorough Review* (40%) — Are all existing rules genuinely reviewed, not just a token subset?
  2. *Correct Flagging* (30%) — Are overly permissive rules correctly identified?
  3. *Correct Remediation* (30%) — Are the rewritten rules appropriately scoped?

---

### Activity 13.5 — Secrets Management with AWS Secrets Manager
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 13.2
- **Evidence Required:** Screenshot + GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Learn AWS Secrets Manager (a step up from GitHub Secrets or plain environment variables — supports automatic rotation, fine-grained access control, and audit logging). Fellow stores a database credential in Secrets Manager, updates their Milestone 6/9 application/Terraform code to fetch it at runtime instead of using an environment variable directly, and explains when Secrets Manager's extra cost/complexity is justified versus when simpler approaches (GitHub Secrets, `.env` files) remain appropriate.
- **Rubric:**
  1. *Correct Implementation* (50%) — Is the secret correctly stored and retrieved at runtime?
  2. *Judgment* (50%) — Is the "when to use which secrets approach" reasoning sound and well-justified?

---

### Activity 13.6 — Debug Challenge: The Exposed Bucket
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 13.4 | **Prerequisites:** Activity 13.4, Activity 13.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a described scenario (platform-provided, echoing many real publicized incidents): a company discovers an S3 bucket containing customer data has been publicly readable for months. Fellow must reason through the full incident response as a cloud engineer (not a security analyst, though the parallel to the Cybersecurity track's IR lifecycle is worth drawing if familiar): immediate containment (restrict access), assess exposure scope (check access logs — connecting to Milestone 12's logging skills), and propose prevention measures (S3 Block Public Access at the account level, automated scanning, IAM policy guardrails).
- **Rubric:**
  1. *Correct Containment Steps* (35%) — Are immediate response actions correctly prioritized?
  2. *Correct Investigation Approach* (30%) — Is the exposure-assessment approach (using logs) sound?
  3. *Correct Prevention Measures* (35%) — Are proposed long-term fixes technically sound and appropriately scoped?

---

### Activity 13.7 — Project: Full Security Hardening Pass
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 13.5 | **Prerequisites:** Activity 13.5, Activity 13.6
- **Evidence Required:** Screenshot + Written Submission + Documentation
- **Review & Collaboration:** None
- **Prompt:** Fellow performs a comprehensive security hardening pass across their Milestone 9/10 Terraform-managed infrastructure: least-privilege IAM throughout, encryption at rest and in transit enabled everywhere applicable, Security Groups tightened to minimum necessary access, secrets moved to Secrets Manager, and S3 Block Public Access enabled account-wide unless deliberately needed. Fellow produces a formal security hardening report (documenting before/after state for each area) — genuinely mirroring the professional report structure from the Cybersecurity track's Milestone 9, if a fellow has taken it, or taught fresh here as a valuable standalone skill.
- **Rubric:**
  1. *Hardening Completeness* (35%) — Are all major areas (IAM, encryption, networking, secrets) genuinely addressed?
  2. *Technical Correctness* (30%) — Are the hardening changes correctly implemented (not broken)?
  3. *Report Quality* (25%) — Is the before/after documentation clear and professional?
  4. *Functionality Preserved* (10%) — Does the infrastructure still work correctly after hardening (security shouldn't mean broken)?

---

### Activity 13.8 — Brand: Thinking Like a Security-Conscious Engineer
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 13.7 | **Prerequisites:** Activity 13.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video on the Full Security Hardening Pass — what was found (including honest admission of what was over-permissioned before, a genuinely valuable and relatable "I made this mistake too" narrative), and what changed. This kind of honest, specific security-improvement writeup is highly respected in the field.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates before/after?
  3. *Honesty & Specificity* (25%) — Does it genuinely and specifically discuss what was wrong, not vague generalities?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 13.9 — Mock Interview: Cloud Security Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 13.6, Activity 13.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 78/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain the Shared Responsibility Model, walk through their IAM audit findings and Security Hardening Pass, discuss the Activity 13.6 exposed-bucket incident response, and reason through when Secrets Manager's added complexity is justified.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 13 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 13.1 | The Shared Responsibility Model | Technical Research | Beginner | 10 | Required |
| 13.2 | IAM Audit: Finding Your Own Over-Permissioned Access | Workshop | Intermediate | 20 | Required |
| 13.3 | Encryption at Rest & in Transit | Workshop | Intermediate | 20 | Required |
| 13.4 | Security Groups & NACLs: The Hardening Pass | Case Study | Intermediate | 15 | Required |
| 13.5 | Secrets Management with AWS Secrets Manager | Workshop | Advanced | 20 | Required |
| 13.6 | Debug Challenge: The Exposed Bucket | Debug Challenge | Advanced | 20 | Required |
| 13.7 | Project: Full Security Hardening Pass | Project | Advanced | 30 | Required |
| 13.8 | Thinking Like a Security-Conscious Engineer (Brand) | Blog Post | Advanced | 15 | Required |
| 13.9 | Mock Interview: Cloud Security Check | Mock Interview | Advanced | 15 | Required |

**Milestone 13 total (if all completed):** 165 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–13)
- Milestones 1–12: 1,945 pts
- Milestone 13: 165 pts
- **Cumulative possible so far:** 2,110 pts

---

## Design Note
Activity 13.2 is deliberately confrontational in a constructive way — it asks fellows to audit and admit their *own* accumulated over-permissioned access from earlier in the track, rather than a hypothetical scenario. This mirrors a genuinely valuable real-world practice (security audits of your own past shortcuts) and produces more honest learning than a purely hypothetical exercise would.

---

## What's Next: Milestone 14 Preview
**Cost Management & Optimization** — the fuller treatment building on Milestone 1's Cost Discipline Gate, covering Reserved Instances/Savings Plans, right-sizing, and building genuine cost-awareness into architectural decisions, not just cleanup habits. Say the word when ready.
