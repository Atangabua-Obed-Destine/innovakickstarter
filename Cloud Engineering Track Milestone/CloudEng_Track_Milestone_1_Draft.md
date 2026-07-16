# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 1: Welcome to Cloud Engineering

**Status:** Draft for review
**Unlocks:** Immediately upon enrollment
**Theme:** Orientation, career paths, and — critically — the **Cost Discipline Gate**: this track's equivalent of Cybersecurity's ethics gate, since real cloud resources cost real money and a forgotten instance can generate a genuine surprise bill. No resource-provisioning activity unlocks until this gate is passed.
**Target fellow:** Zero prior cloud, Linux, or networking experience assumed.

> **Design note:** Same structural pattern as Cybersecurity's Ethics Gate — Activity 1.3 should ideally block resource-provisioning activities **track-wide**, not just in this Milestone. Recommend the same system-level enforcement flag rather than manually wiring prerequisites into every future Milestone.

---

### Activity 1.1 — What Is Cloud Engineering, Really?
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** None
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reads/watches a curated overview of what cloud engineers actually do — provisioning and maintaining infrastructure, automating deployments, ensuring reliability and cost-efficiency, often invisible to end users but critical to everything running smoothly. Write a short reflection on which part excites them most, plus one example of a "the app was down" or "the website was slow" moment they've experienced as a user, now reasoned through from an infrastructure lens (what might have gone wrong behind the scenes).
- **Rubric:**
  1. *Understanding* (60%) — Does the reflection show real grasp of what cloud engineering involves?
  2. *Applied Reasoning* (40%) — Is the "behind the scenes" reasoning about their chosen example plausible?

---

### Activity 1.2 — Career Paths in Cloud Engineering
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.1 | **Prerequisites:** Activity 1.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches the four paths this track eventually branches into — Cloud Architecture, DevOps/SRE, Platform Engineering, and Cloud Security — and writes a short comparison covering what a typical day looks like in each, and which currently appeals to them most (not a binding choice).
- **Rubric:**
  1. *Accuracy* (60%) — Are the four paths correctly and distinctly described?
  2. *Self-Reflection* (40%) — Is their stated interest reasoned, not arbitrary?

---

### Activity 1.3 — The Cost Discipline Gate ⚠️ (Hard Prerequisite)
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** N/A (this activity cannot be skipped or late-penalized around — it blocks progress entirely until passed)
- **Chain Parent:** None | **Prerequisites:** Activity 1.1
- **Evidence Required:** Written Submission + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Fellow works through material covering: how cloud billing actually works (pay-per-use, easy to underestimate), real anonymized "surprise bill" horror stories (a forgotten instance, an accidental infinite loop spinning up resources, an exposed API key used by someone else), the AWS Free Tier's specific limits and what happens when you exceed them, and the non-negotiable habit of **always tearing down resources you're not actively using**. Fellow then sets up a real AWS Billing Alert/Budget (e.g., alert at $1) on their own account and screenshots the configured alert as proof, plus answers 5 scenario questions ("would this action risk unexpected charges?") with a required 100% pass rate — this is not a partial-credit topic.
- **Rubric:**
  1. *Scenario Accuracy* (60%) — All 5 scenarios must be correctly identified to unlock further resource-provisioning activities.
  2. *Billing Alert Setup* (40%) — Is a real, functioning billing alert correctly configured?
- **Passing requirement:** 100% on scenario questions to unlock any resource-provisioning activity in the track. Retakes allowed — this is a learning gate, not a punitive one.

---

### Activity 1.4 — AWS Account Setup
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.3 | **Prerequisites:** Activity 1.3 (gated)
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Create a free-tier AWS account (with the billing alert from 1.3 already active), enable multi-factor authentication (MFA) on the root account — a critical security habit from day one — and create a personal IAM user (never operate day-to-day as root, another habit worth instilling immediately). Screenshot the IAM user successfully logged in and MFA confirmation.
- **Rubric:**
  1. *Completeness* (60%) — Is the account correctly set up with MFA enabled?
  2. *IAM Best Practice* (40%) — Is a non-root IAM user correctly created and used for login?

---

### Activity 1.5 — Your First Cloud Resource: Launch & Terminate
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 1.4 | **Prerequisites:** Activity 1.4, Activity 1.3 (gated)
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Launch a free-tier-eligible EC2 instance through the AWS Console (guided, low-stakes first touch of real cloud compute), connect to it, then — the actually important part — **properly terminate it** and verify no billable resources remain (checking for lingering EBS volumes/Elastic IPs, common sources of surprise charges even after "deleting" an instance). Screenshot both the running instance and the confirmed-clean state afterward, with a short note on what could have been left behind if they'd only stopped (not terminated) the instance.
- **Rubric:**
  1. *Correct Launch* (30%) — Is the instance correctly launched and connected to?
  2. *Correct Cleanup* (50%) — Is everything genuinely torn down, with no lingering billable resources?
  3. *Cost-Awareness Reasoning* (20%) — Is the "what could have been left behind" reasoning correct?

---

### Activity 1.6 — Case Study: The Surprise Bill
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (gated)
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches one real, publicly documented case of an individual or small company receiving an unexpectedly large cloud bill (several well-known ones are publicly written up by their own authors as cautionary blog posts) and writes a short case study: what caused it, what safeguards were missing, and — most importantly — which of those safeguards they've now personally set up (from Activities 1.3–1.5) that would have prevented it.
- **Rubric:**
  1. *Case Understanding* (50%) — Is the real incident accurately summarized?
  2. *Applied Prevention* (50%) — Is the connection to their own now-configured safeguards correct and specific?

---

### Activity 1.7 — Community Contribution: Share Your Cost-Safety Checklist
- **Activity Type:** Community Contribution
- **Pillar:** Collaborate
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 1.6 | **Prerequisites:** Activity 1.6
- **Evidence Required:** Written Submission + URL/Link
- **Review & Collaboration:** ✅ Collaborative Activity
- **Prompt:** Fellow compiles everything learned in this Milestone into a personal "Cloud Cost-Safety Checklist" (billing alerts, MFA, always-terminate-not-stop, tag resources, review the bill weekly) and posts it to the platform's fellow community space (forum/Slack/Discord channel — wherever I-NNOVA KICKSTARTER hosts peer discussion) for other fellows starting this track to use. A genuinely useful first act of community contribution, not busywork.
- **Rubric:**
  1. *Checklist Quality* (60%) — Is it complete, accurate, and genuinely usable by another fellow?
  2. *Community Sharing* (40%) — Is it actually posted where other fellows can find and benefit from it?

---

### Activity 1.8 — Brand: My Cloud Journey Begins
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.5 | **Prerequisites:** Activity 1.5
- **Evidence Required:** URL/Link (article) **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video on launching (and properly cleaning up) a first-ever cloud resource, and why cost discipline is treated as seriously as it is in this track from day one. Same tagging convention as your other tracks.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, well-structured writeup?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Authenticity* (30%) — Genuine, personal explanation, not generic copy?

---

### Activity 1.9 — Mock Interview: Orientation & Cost Discipline Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.6, Activity 1.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Behavioral questions ("why cloud engineering?"), a couple of cost-scenario questions echoing Activity 1.3, and a walkthrough of their Activity 1.6 case study findings.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 1 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 1.1 | What Is Cloud Engineering, Really? | Reflection | Beginner | 5 | Required |
| 1.2 | Career Paths in Cloud Engineering | Technical Research | Beginner | 5 | Required |
| 1.3 | The Cost Discipline Gate ⚠️ | Workshop | Beginner | 15 | Required — Hard Gate |
| 1.4 | AWS Account Setup | Workshop | Beginner | 10 | Required |
| 1.5 | Your First Cloud Resource: Launch & Terminate | Project | Beginner | 15 | Required |
| 1.6 | Case Study: The Surprise Bill | Case Study | Beginner | 15 | Required |
| 1.7 | Share Your Cost-Safety Checklist | Community Contribution | Beginner | 10 | Required |
| 1.8 | My Cloud Journey Begins (Brand) | Blog Post | Beginner | 10 | Required |
| 1.9 | Mock Interview: Orientation & Cost Discipline Check | Mock Interview | Beginner | 10 | Required |

**Milestone 1 total (if all completed):** 95 points (raw, pre-multiplier)

---

## Platform/Operational Notes
1. **Cost Discipline Gate enforcement** — same flag as the Cybersecurity Ethics Gate: ideally blocks *every* resource-provisioning activity across the whole track, not just Milestone 1. System-level design decision, not per-Milestone prerequisite wiring.
2. **Real billing risk is real** — worth confirming AWS Free Tier limits are still accurate/sufficient at build time (AWS periodically adjusts Free Tier terms), and whether I-NNOVA CM wants to set an institutional spending cap or monitoring layer for fellows' personal AWS accounts, given real money is now in the loop for the first time across all four tracks.
3. **Activity Type taxonomy** — this document uses your platform's actual Activity Types (Reflection, Technical Research, Workshop, Project, Case Study, Community Contribution, Blog Post, Mock Interview) and Review & Collaboration flags, rather than the generic labels used in earlier SWE/Cybersecurity/Data Science drafts. Let me know if you'd like those retroactively updated to match.

---

## What's Next: Milestone 2 Preview
**Linux & Command Line Fundamentals** — self-contained from scratch (filesystem, permissions, users, shell scripting), since cloud work lives in the terminal via SSH constantly. Say the word when ready.
