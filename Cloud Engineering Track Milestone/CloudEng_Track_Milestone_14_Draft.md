# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 14: Cost Management & Optimization

**Status:** Draft for review
**Unlocks:** After Milestone 13 is fully completed
**Theme:** The fuller treatment building on Milestone 1's Cost Discipline Gate. Where Milestone 1 taught "don't forget to clean up," this Milestone teaches genuine cost-architecture thinking — right-sizing, pricing models, and building cost-awareness into design decisions from the start, not just cleanup afterward.
**Target fellow:** Has built and torn down infrastructure across every AWS service in the track; understands cost discipline as a habit, ready to understand it as a design discipline.

---

### Activity 14.1 — From Habit to Architecture: Cost as a Design Input
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 13.7
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on the shift from Milestone 1's "remember to terminate resources" habit to this Milestone's deeper question: could the same architecture have been designed more cost-efficiently from the start? Fellow picks one prior Milestone's project (e.g., Milestone 5's Highly Available Web Application) and speculates on what cost tradeoffs were made without full awareness at the time.
- **Rubric:**
  1. *Understanding* (60%) — Is the "cleanup habit vs. cost architecture" distinction correctly grasped?
  2. *Applied Reflection* (40%) — Is the retrospective analysis of a chosen prior project genuine and specific?

---

### Activity 14.2 — AWS Pricing Models: On-Demand, Reserved, Spot, Savings Plans
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 14.1 | **Prerequisites:** Activity 14.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the major EC2 pricing models: On-Demand (pay per hour, used throughout the track), Reserved Instances/Savings Plans (committing to 1-3 years for a discount — appropriate for predictable, steady workloads), and Spot Instances (bidding on spare capacity at steep discounts, but can be reclaimed with little warning — appropriate only for fault-tolerant, interruptible workloads). Fellow matches each pricing model to 3 different workload scenarios (a steady internal company tool, a batch data-processing job that can restart if interrupted, a short-lived dev/test environment) with correct reasoning.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Are the pricing models correctly explained?
  2. *Correct Matching* (40%) — Are scenarios correctly matched to the appropriate pricing model?

---

### Activity 14.3 — Right-Sizing: Are You Overpaying for Capacity?
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 14.2 | **Prerequisites:** Activity 14.2, Activity 12.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn right-sizing — using actual CloudWatch metrics (Milestone 12) to determine if an instance is over-provisioned (consistently low CPU/memory usage relative to its size) or under-provisioned. Fellow launches an oversized instance for a trivial workload, observes the resulting low utilization in CloudWatch over a short period, and documents the right-sizing recommendation they'd make (a smaller instance type) with the reasoning based on actual observed data, not guesswork. **Terminate when done.**
- **Rubric:**
  1. *Correct Data-Driven Analysis* (60%) — Is the right-sizing recommendation genuinely based on observed CloudWatch data?
  2. *Reasoning Quality* (20%) — Is the reasoning clearly explained?
  3. *Cost Discipline* (20%) — Is the instance correctly terminated?

---

### Activity 14.4 — AWS Cost Explorer & Cost Allocation Tags
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 14.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn Cost Allocation Tags — labeling resources (e.g., `Project: Milestone13`, `Environment: learning`) so costs can be broken down and attributed, essential once an account has more than a handful of resources (or, in a real company, many teams sharing one AWS bill). Fellow tags a small set of resources consistently, and uses AWS Cost Explorer to filter and view costs by tag, demonstrating how this would let a real team answer "which project/team is driving our AWS bill" — a genuinely common real-world question this Milestone directly prepares fellows to answer.
- **Rubric:**
  1. *Correct Tagging* (40%) — Are resources correctly and consistently tagged?
  2. *Correct Cost Explorer Usage* (40%) — Is filtering by tag correctly demonstrated?
  3. *Applied Reasoning* (20%) — Is the "why this matters at scale" explanation sound?

---

### Activity 14.5 — Serverless as a Cost Optimization: Intro to Lambda
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 14.2
- **Evidence Required:** Screenshot + GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Learn AWS Lambda's core value proposition through a cost lens — pay only for actual execution time (milliseconds), not for an always-running server, ideal for infrequent or bursty workloads. Fellow writes a small Lambda function (e.g., a scheduled function that checks and reports on something simple, like S3 bucket sizes), deploys it, and compares — in writing — the cost model of running this same logic on an always-on EC2 instance versus Lambda for a genuinely low-frequency task, quantifying the difference.
- **Rubric:**
  1. *Correct Lambda Implementation* (50%) — Is the function correctly written and deployed?
  2. *Cost Comparison Quality* (50%) — Is the EC2-vs-Lambda cost comparison genuinely quantified and correctly reasoned?

---

### Activity 14.6 — Case Study: The $50,000 Mistake
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 14.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches a real, publicly documented large-scale cloud cost incident (several exist at genuinely dramatic dollar amounts, often architectural mistakes rather than simple forgotten resources — e.g., a recursive Lambda invocation loop, an accidentally-public data transfer pattern) and writes a case study connecting it to specific concepts from this Milestone and Milestone 1 that would have caught or prevented it (billing alerts, architectural review, right-sizing, tagging for visibility).
- **Rubric:**
  1. *Case Understanding* (50%) — Is the real incident accurately summarized?
  2. *Applied Prevention* (50%) — Is the connection to specific cost-management practices correct and well-reasoned?

---

### Activity 14.7 — Project: Cost Optimization Audit & Report
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 14.4 | **Prerequisites:** Activity 14.3, Activity 14.4, Activity 14.5
- **Evidence Required:** Written Submission + Presentation/Slide Deck
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a described (fictional but realistic) small company's AWS architecture and monthly cost breakdown, and must produce a full cost optimization audit report: which resources are likely over-provisioned, which pricing model changes would help (Reserved Instances, Spot, Lambda migration candidates), and a projected savings estimate with clear reasoning — presented as a slide deck a real client (echoing I-NNOVA CM's own client relationships) could actually receive and act on.
- **Rubric:**
  1. *Diagnostic Quality* (35%) — Are the identified optimization opportunities genuinely sound?
  2. *Recommendation Quality* (30%) — Are proposed changes technically correct and appropriately justified?
  3. *Savings Estimate Reasoning* (20%) — Is the projected savings calculation reasonable and clearly shown?
  4. *Presentation Quality* (15%) — Is the deck genuinely client-ready?

---

### Activity 14.8 — Brand: The Business Case for Cost Engineering
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 14.7 | **Prerequisites:** Activity 14.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video on the Cost Optimization Audit project — what was found, what was recommended, and why cost-awareness is a genuine, valuable, differentiating engineering skill (not just an accounting concern). A great piece to demonstrate business-mindedness alongside technical skill.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, presents findings compellingly?
  3. *Business Framing* (25%) — Does it correctly frame cost engineering as a genuine value-add skill?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 14.9 — Mock Interview: Cost Optimization Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 14.6, Activity 14.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 78/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain the different EC2 pricing models and when each fits, walk through their Cost Optimization Audit findings and recommendations, discuss the Activity 14.6 case study, and reason through when serverless genuinely saves money versus when it doesn't.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 14 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 14.1 | From Habit to Architecture: Cost as a Design Input | Reflection | Beginner | 5 | Required |
| 14.2 | AWS Pricing Models | Technical Research | Beginner | 15 | Required |
| 14.3 | Right-Sizing | Workshop | Intermediate | 20 | Required |
| 14.4 | Cost Explorer & Cost Allocation Tags | Workshop | Intermediate | 20 | Required |
| 14.5 | Serverless as a Cost Optimization: Intro to Lambda | Workshop | Intermediate | 20 | Required |
| 14.6 | Case Study: The $50,000 Mistake | Case Study | Intermediate | 15 | Required |
| 14.7 | Project: Cost Optimization Audit & Report | Project | Advanced | 25 | Required |
| 14.8 | The Business Case for Cost Engineering (Brand) | Blog Post | Advanced | 15 | Required |
| 14.9 | Mock Interview: Cost Optimization Check | Mock Interview | Advanced | 15 | Required |

**Milestone 14 total (if all completed):** 150 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–14)
- Milestones 1–13: 2,110 pts
- Milestone 14: 150 pts
- **Cumulative possible so far:** 2,260 pts

---

## This Closes the Operational Maturity Arc
Milestones 12–14 (Observability, Security, Cost Optimization) represent the "operational maturity" layer sitting on top of the raw infrastructure and deployment skills built in Milestones 4–11. Fellows can now not just build and deploy infrastructure, but monitor it, secure it, and optimize its cost — a genuinely complete cloud engineering skill set at the foundations level.

---

## What's Next: Milestone 15 — The Foundations Capstone
A single, comprehensive project bringing together every skill from Milestones 1–14: custom VPC, containerized application, Terraform-provisioned infrastructure, full CI/CD pipeline, Kubernetes deployment, observability, security hardening, and cost optimization — before branching into Cloud Architecture / DevOps-SRE / Platform Engineering / Cloud Security specializations. Say the word when ready to build it.
