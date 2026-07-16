# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 4: AWS Fundamentals & Core Services

**Status:** Draft for review
**Unlocks:** After Milestone 3 is fully completed
**Theme:** The track becomes concretely AWS-specific. IAM in real depth, regions/availability zones, EC2 revisited properly (beyond just launch/terminate), and S3 basics — the four services almost every other AWS Milestone builds on top of.
**Target fellow:** Cost-gated, Linux-fluent, networking-literate; has only touched AWS at a surface level (Milestone 1's guided EC2 launch).

---

### Activity 4.1 — The AWS Global Infrastructure
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Cost Discipline Gate)
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn AWS's global structure: Regions, Availability Zones (AZs) within a Region, and Edge Locations. Fellow explains why a region closer to end users typically means lower latency, why spreading resources across multiple AZs improves reliability, and picks (with reasoning) which AWS region would make the most sense for a hypothetical Cameroonian e-commerce app's primary user base.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Are Region/AZ/Edge Location correctly distinguished?
  2. *Applied Reasoning* (40%) — Is the region choice for the hypothetical scenario well-justified?

---

### Activity 4.2 — IAM Deep Dive: Users, Groups, Roles & Policies
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.1 | **Prerequisites:** Activity 4.1, Activity 1.4
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Go beyond Milestone 1's single IAM user setup: learn IAM Groups (for managing permissions at scale), IAM Roles (for granting temporary, revocable access — including to AWS services themselves, not just people), and JSON policy documents (the actual permission-granting mechanism). Fellow creates an IAM group with a scoped policy (e.g., read-only S3 access), adds a user to it, and writes a short explanation of the principle of least privilege as applied to their specific policy choice — plus why Roles are preferred over long-lived access keys wherever possible.
- **Rubric:**
  1. *Correct IAM Setup* (50%) — Are the group/policy correctly and appropriately scoped?
  2. *Least Privilege Reasoning* (30%) — Is the scoping justification sound?
  3. *Roles vs. Keys Understanding* (20%) — Is the reasoning for preferring Roles correctly explained?

---

### Activity 4.3 — EC2 Revisited: Instance Types & Configuration
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.2 | **Prerequisites:** Activity 4.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn instance type families (general purpose, compute-optimized, memory-optimized — and how to reason about which fits a workload, not just default to whatever's free-tier eligible in real jobs), AMIs (Amazon Machine Images), user data scripts (bootstrapping an instance at launch), and Elastic IPs. Fellow launches an EC2 instance using a user data script that automatically installs and starts a simple web server (e.g., nginx) with zero manual SSH configuration needed, verifies it's serving a page, screenshots proof, then **terminates it**.
- **Rubric:**
  1. *Correct User Data Script* (50%) — Does the bootstrap script correctly install and start the web server automatically?
  2. *Instance Type Reasoning* (30%) — Is the chosen instance type reasoning sound for this workload?
  3. *Cost Discipline* (20%) — Is the instance correctly terminated afterward?

---

### Activity 4.4 — S3 Fundamentals: Object Storage
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.2
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn S3 buckets, objects, and the crucial concept of bucket permissions/public access blocks (a genuinely common, genuinely dangerous real-world misconfiguration — publicly exposed S3 buckets have caused major real breaches). Fellow creates a private S3 bucket, uploads a test file, confirms it's NOT publicly accessible (verifying the block), then deliberately (in a throwaway test bucket only) makes a single object public to see the difference, and immediately reverts it — screenshotting both states with a note on why "S3 bucket set to public by accident" is such a common real incident.
- **Rubric:**
  1. *Correct Bucket Setup* (50%) — Is the bucket correctly created with private-by-default access confirmed?
  2. *Public Access Understanding* (50%) — Is the public/private distinction correctly demonstrated and explained?

---

### Activity 4.5 — Billing Explorer: Reading Your Own Usage
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.3, Activity 4.4
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Revisiting Milestone 1's cost discipline theme with real usage data now available: fellow opens the AWS Billing Dashboard / Cost Explorer, identifies which services have generated any charges/Free Tier usage so far (EC2, S3), and writes a short note on what they'd check weekly if they were managing this account long-term.
- **Rubric:**
  1. *Correct Navigation* (50%) — Is the Billing Dashboard/Cost Explorer correctly used to identify usage?
  2. *Ongoing Habit Reasoning* (50%) — Is the "what to check weekly" reasoning sound and specific?

---

### Activity 4.6 — Debug Challenge: The Locked-Out Engineer
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.2 | **Prerequisites:** Activity 4.2, Activity 4.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a described scenario: an IAM user can log into the AWS Console but gets "Access Denied" trying to launch an EC2 instance. Fellow reasons through likely causes (missing EC2 permissions in the attached policy, a policy that's too narrowly scoped, an explicit deny somewhere overriding an allow) and proposes the correct diagnostic steps and fix, applying IAM policy reasoning from Activity 4.2.
- **Rubric:**
  1. *Diagnostic Reasoning* (50%) — Is the likely cause correctly reasoned through given IAM policy logic?
  2. *Correct Fix Proposal* (50%) — Is the proposed fix technically sound?

---

### Activity 4.7 — Project: Self-Hosted Static Website on S3
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.4 | **Prerequisites:** Activity 4.4, Activity 4.5
- **Evidence Required:** URL/Link + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Configure an S3 bucket for static website hosting (a genuinely common, genuinely cheap real-world use case), upload a simple HTML/CSS page (their own, or reuse something from a prior track if they've done Software Engineering), and get it live at the S3-provided URL. Fellow documents the specific, minimal bucket policy required to make *only this* website publicly readable — deliberately contrasting with Activity 4.4's "don't make things public by accident" lesson: this time, public access is a deliberate, scoped, justified choice.
- **Rubric:**
  1. *Correct Configuration* (50%) — Is static website hosting correctly enabled and functional?
  2. *Scoped Public Access* (30%) — Is the bucket policy minimal and deliberate, not overly broad?
  3. *Documentation* (20%) — Is the reasoning for this specific public-access choice clearly explained?

---

### Activity 4.8 — Brand: My First Real AWS Services
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.7 | **Prerequisites:** Activity 4.7, Activity 4.6
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video covering the S3 static website project — how it was configured, the deliberate-vs-accidental public access distinction from this Milestone, and a live demo of the hosted site. A strong, genuinely useful portfolio piece since "deploy a static site to S3" is a real, common task.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality, live demo shown?
  3. *Technical Accuracy* (25%) — Correctly explains the public access configuration?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 4.9 — Mock Interview: AWS Fundamentals Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.6, Activity 4.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain Regions vs. AZs, IAM policy/least-privilege reasoning, walk through the S3 static website project's access configuration, and discuss the Activity 4.6 IAM troubleshooting scenario.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 4 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 4.1 | The AWS Global Infrastructure | Technical Research | Beginner | 10 | Required |
| 4.2 | IAM Deep Dive | Workshop | Intermediate | 20 | Required |
| 4.3 | EC2 Revisited: Instance Types & Configuration | Workshop | Intermediate | 20 | Required |
| 4.4 | S3 Fundamentals: Object Storage | Workshop | Beginner | 15 | Required |
| 4.5 | Billing Explorer: Reading Your Own Usage | Workshop | Beginner | 10 | Required |
| 4.6 | Debug Challenge: The Locked-Out Engineer | Debug Challenge | Intermediate | 20 | Required |
| 4.7 | Project: Self-Hosted Static Website on S3 | Project | Intermediate | 25 | Required |
| 4.8 | My First Real AWS Services (Brand) | Blog Post | Intermediate | 15 | Required |
| 4.9 | Mock Interview: AWS Fundamentals Check | Mock Interview | Intermediate | 10 | Required |

**Milestone 4 total (if all completed):** 145 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–4)
- Milestone 1: 95 pts
- Milestone 2: 115 pts
- Milestone 3: 110 pts
- Milestone 4: 145 pts
- **Cumulative possible so far:** 465 pts

---

## What's Next: Milestone 5 Preview
**Compute Deep Dive** — Auto Scaling Groups, Elastic Load Balancing, and designing for actual reliability (not just "one EC2 instance running") — the real difference between a toy deployment and something production-grade. Say the word when ready.
