# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 5: Compute Deep Dive

**Status:** Draft for review
**Unlocks:** After Milestone 4 is fully completed
**Theme:** The real difference between a toy deployment (one EC2 instance) and something production-grade — Auto Scaling Groups, Elastic Load Balancing, health checks, and designing for actual reliability rather than hoping a single server never fails.
**Target fellow:** AWS-fluent on IAM/EC2/S3 basics; has never designed for high availability.

---

### Activity 5.1 — Why One Server Is Never Enough
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on their Milestone 4 single-EC2 web server: what happens if that instance crashes, or gets overwhelmed by a sudden traffic spike? Fellow reasons through why a single point of failure is unacceptable for any real production system, and connects this to a real-world scenario relevant to I-NNOVA CM's own context (e.g., what would happen if a school's fee-payment portal went down during registration week).
- **Rubric:**
  1. *Understanding* (60%) — Is the single-point-of-failure risk correctly reasoned through?
  2. *Applied Scenario* (40%) — Is the real-world consequence scenario plausible and well-reasoned?

---

### Activity 5.2 — Health Checks & Instance Recovery
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.1 | **Prerequisites:** Activity 5.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn what a health check actually is (a periodic request confirming an instance is genuinely serving traffic correctly, not just "powered on") and why "the instance is running" and "the instance is healthy" are different claims. Fellow explains 3 different things a health check could verify beyond basic uptime (e.g., can it reach the database, does a specific endpoint return 200, is response time acceptable) and why each matters.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the running-vs-healthy distinction correctly explained?
  2. *Applied Examples* (40%) — Are the 3 health check examples genuinely meaningful, not superficial?

---

### Activity 5.3 — Elastic Load Balancing Fundamentals
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.2 | **Prerequisites:** Activity 5.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn what an Application Load Balancer (ALB) does — distributing incoming traffic across multiple targets, health-checking them, routing away from unhealthy ones. Fellow launches 2 EC2 instances (each with the Milestone 4 user-data web server bootstrap), places both behind a new ALB, configures the health check, and demonstrates the ALB correctly routing traffic across both — then deliberately stops the web server process on one instance and shows the ALB detecting it as unhealthy and routing only to the healthy one. **Terminate everything when done.**
- **Rubric:**
  1. *Correct ALB Setup* (40%) — Is the load balancer correctly configured and routing traffic?
  2. *Health Check Behavior Demonstrated* (40%) — Is the unhealthy-instance detection and rerouting correctly shown?
  3. *Cost Discipline* (20%) — Are all resources correctly torn down afterward?

---

### Activity 5.4 — Auto Scaling Groups
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.3 | **Prerequisites:** Activity 5.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn Auto Scaling Groups (ASGs): launch templates, minimum/desired/maximum capacity, and scaling policies (e.g., scale out when average CPU exceeds a threshold). Fellow creates a launch template based on Milestone 4's bootstrapped web server, configures an ASG with a small capacity range (e.g., min 1, desired 2, max 3) attached to the Activity 5.3 load balancer, and demonstrates the ASG maintaining desired capacity by manually terminating one instance and observing the ASG automatically replace it. **Tear everything down afterward.**
- **Rubric:**
  1. *Correct ASG Configuration* (40%) — Is the ASG correctly configured with sensible capacity settings?
  2. *Self-Healing Demonstrated* (40%) — Is automatic instance replacement correctly shown after manual termination?
  3. *Cost Discipline* (20%) — Is everything correctly torn down afterward?

---

### Activity 5.5 — Scaling Policies & Cost Tradeoffs
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 5.4
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the tradeoff at the heart of auto scaling: scale too conservatively and risk poor performance during traffic spikes; scale too aggressively and pay for capacity that isn't needed (tying back to Milestone 1's cost discipline theme). Given 3 different fictional business scenarios (a flash-sale e-commerce site, a steady-traffic internal company tool, a school portal with predictable registration-week spikes), fellow proposes appropriate min/max capacity ranges and scaling triggers for each, justifying the cost-vs-reliability tradeoff made in each case.
- **Rubric:**
  1. *Scenario-Appropriate Reasoning* (60%) — Are the proposed scaling configurations sensible for each scenario's traffic pattern?
  2. *Cost-Reliability Tradeoff Justification* (40%) — Is the reasoning behind each tradeoff clearly and correctly explained?

---

### Activity 5.6 — Debug Challenge: The Scaling Storm
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.4 | **Prerequisites:** Activity 5.4, Activity 5.5
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a described scenario: an ASG is rapidly scaling up and down repeatedly ("flapping") instead of stabilizing, and costs are climbing unexpectedly. Fellow reasons through likely causes (scaling thresholds too tight/no cooldown period, a health check misconfiguration causing false negatives, a scaling metric that's inherently noisy) and proposes the correct diagnostic approach and fix.
- **Rubric:**
  1. *Diagnostic Reasoning* (50%) — Are plausible causes for the flapping behavior correctly identified?
  2. *Correct Fix Proposal* (50%) — Is the proposed fix (cooldowns, better health checks, smoothed metrics) technically sound?

---

### Activity 5.7 — Project: Highly Available Web Application
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.4 | **Prerequisites:** Activity 5.4, Activity 5.6
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Bring together everything from this Milestone: deploy a load-balanced, auto-scaled web application spread across **multiple Availability Zones** (a new requirement — true high availability means surviving an entire AZ outage, not just an instance failure), with a documented architecture explanation of every design decision (why this capacity range, why these AZs, why this health check config). **Tear everything down after demonstrating it works**, and document the full teardown in the submission.
- **Rubric:**
  1. *Multi-AZ Architecture* (30%) — Is the deployment genuinely spread across multiple AZs?
  2. *Functional Auto Scaling & Load Balancing* (30%) — Does everything work together correctly?
  3. *Architectural Documentation* (25%) — Are design decisions clearly and correctly justified?
  4. *Cost Discipline* (15%) — Is complete teardown documented?

---

### Activity 5.8 — Brand: Building for Reliability
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.7 | **Prerequisites:** Activity 5.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video walking through the Highly Available Web Application project — the architecture, the multi-AZ reasoning, and a demonstration (screen recording) of the self-healing behavior from Activity 5.4 in action. A genuinely strong portfolio piece demonstrating production-grade thinking.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates real self-healing behavior?
  3. *Architectural Explanation* (25%) — Correctly and clearly explains the design decisions?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 5.9 — Mock Interview: Compute & Reliability Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.6, Activity 5.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 72/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain how load balancers and auto scaling work together, walk through their Highly Available Web Application's architecture, discuss the Activity 5.6 scaling-storm scenario, and reason through cost-vs-reliability tradeoffs.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 5 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 5.1 | Why One Server Is Never Enough | Reflection | Beginner | 5 | Required |
| 5.2 | Health Checks & Instance Recovery | Technical Research | Beginner | 10 | Required |
| 5.3 | Elastic Load Balancing Fundamentals | Workshop | Intermediate | 25 | Required |
| 5.4 | Auto Scaling Groups | Workshop | Advanced | 25 | Required |
| 5.5 | Scaling Policies & Cost Tradeoffs | Case Study | Intermediate | 15 | Required |
| 5.6 | Debug Challenge: The Scaling Storm | Debug Challenge | Advanced | 20 | Required |
| 5.7 | Project: Highly Available Web Application | Project | Advanced | 30 | Required |
| 5.8 | Building for Reliability (Brand) | Blog Post | Advanced | 15 | Required |
| 5.9 | Mock Interview: Compute & Reliability Check | Mock Interview | Advanced | 15 | Required |

**Milestone 5 total (if all completed):** 160 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–5)
- Milestone 1: 95 pts
- Milestone 2: 115 pts
- Milestone 3: 110 pts
- Milestone 4: 145 pts
- Milestone 5: 160 pts
- **Cumulative possible so far:** 785 pts

---

## Cost Discipline Note
This Milestone involves the most real, running infrastructure yet (multiple EC2 instances, a Load Balancer, an ASG) — all still Free Tier-eligible at small scale, but worth double-checking Free Tier limits specifically around ALB hours and multiple concurrent instances at build time, and reinforcing teardown requirements even more explicitly than earlier Milestones (built into every relevant rubric above).

---

## What's Next: Milestone 6 Preview
**Storage & Databases in the Cloud** — S3 deep dive (versioning, lifecycle policies), EBS volumes, RDS (managed relational databases), and an intro to DynamoDB — moving from "where does compute live" to "where does data live." Say the word when ready.
