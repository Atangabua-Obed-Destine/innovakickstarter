# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 12: Monitoring, Logging & Observability

**Status:** Draft for review
**Unlocks:** After Milestone 11 is fully completed
**Theme:** Seeing what infrastructure is actually doing, rather than hoping it works. CloudWatch metrics/alarms, centralized logging, and the mindset shift from "is it running?" to "is it healthy, and will I know the moment it isn't?"
**Target fellow:** Has deployed infrastructure across EC2, containers, and Kubernetes; has never set up monitoring or alerting.

---

### Activity 12.1 — Why "It's Running" Isn't Good Enough
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 11.8
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on every prior Milestone's projects — how would they actually know, right now, if any of them broke overnight? (Most wouldn't know unless they manually checked.) Fellow researches the observability mindset (metrics, logs, traces — the "three pillars") and explains why waiting for a user to report a problem is the worst possible way to find out about an outage.
- **Rubric:**
  1. *Understanding* (60%) — Is the observability mindset correctly explained?
  2. *Applied Reflection* (40%) — Is the "how would I know" reflection genuine and specific to their own past work?

---

### Activity 12.2 — CloudWatch Metrics Fundamentals
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 12.1 | **Prerequisites:** Activity 12.1
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn CloudWatch's default metrics (CPU utilization, network I/O, disk usage) automatically collected for EC2 and other services. Fellow launches a free-tier EC2 instance, generates some CPU load (a simple stress-test command), and screenshots the CloudWatch metrics dashboard showing the CPU spike in near-real-time, explaining what default metrics exist for the resource types used throughout the track (EC2, RDS, ALB). **Terminate the instance when done.**
- **Rubric:**
  1. *Correct Metric Observation* (50%) — Is the CPU spike correctly generated and observed in CloudWatch?
  2. *Metric Awareness* (30%) — Are default metrics for other track-relevant services correctly identified?
  3. *Cost Discipline* (20%) — Is the instance correctly terminated?

---

### Activity 12.3 — CloudWatch Alarms & Notifications
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 12.2 | **Prerequisites:** Activity 12.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn CloudWatch Alarms (triggering based on a metric threshold) and SNS (Simple Notification Service, for sending an email/SMS when an alarm fires). Fellow configures an alarm on CPU utilization with a low threshold, wires it to an SNS topic delivering to their own email, deliberately triggers it (via the same stress test from 12.2), and screenshots the received notification — directly extending Milestone 1's billing alert concept to general infrastructure health.
- **Rubric:**
  1. *Correct Alarm Configuration* (50%) — Is the alarm correctly configured with a sensible threshold?
  2. *Notification Delivery Demonstrated* (50%) — Is the actual alert notification correctly received and shown?

---

### Activity 12.4 — Centralized Logging with CloudWatch Logs
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 12.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn why centralized logging matters (SSHing into individual servers to check logs doesn't scale, and logs disappear when instances/pods are terminated — a real problem given how often fellows have already torn down resources across this track). Fellow configures the CloudWatch Logs agent on an EC2 instance to ship application logs centrally, generates some log activity, and uses CloudWatch Logs Insights to run a query filtering for specific events (e.g., error-level log lines) across the centralized logs.
- **Rubric:**
  1. *Correct Log Shipping* (50%) — Is centralized logging correctly configured and working?
  2. *Correct Query Usage* (50%) — Is Logs Insights correctly used to filter/find relevant log entries?

---

### Activity 12.5 — Dashboards: Visualizing System Health
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 12.3 | **Prerequisites:** Activity 12.3, Activity 12.4
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn CloudWatch Dashboards — combining multiple metrics/logs into a single at-a-glance view (echoing the data-visualization "choose the right chart" thinking, if fellows have taken the Data Science track). Fellow builds a dashboard combining CPU, network, and a log-based metric into one view that a non-technical stakeholder could glance at to understand "is everything okay."
- **Rubric:**
  1. *Dashboard Completeness* (50%) — Are relevant, meaningful metrics/logs included?
  2. *Clarity for Non-Technical Audience* (50%) — Is the dashboard genuinely readable at a glance, not just a data dump?

---

### Activity 12.6 — Case Study: The Outage Nobody Noticed
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 12.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches a real, publicly documented incident where an outage went undetected for an unusually long time due to inadequate monitoring/alerting, and writes a case study on what specific monitoring gap allowed this, and which of this Milestone's tools (alarms, centralized logs, dashboards) would have caught it sooner.
- **Rubric:**
  1. *Case Understanding* (50%) — Is the real incident accurately summarized?
  2. *Applied Prevention* (50%) — Is the connection to specific monitoring tools correct and well-reasoned?

---

### Activity 12.7 — Debug Challenge: Diagnose from the Dashboard
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 12.5 | **Prerequisites:** Activity 12.5, Activity 12.6
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a described scenario with only dashboard/log data provided (platform-generated: a spike in error-rate logs coinciding with a memory metric climbing steadily, no direct server access given) and must diagnose the likely root cause (e.g., a memory leak) purely from the observability data provided — practicing the real skill of diagnosing without SSHing in first, since dashboards should often get you most of the way there.
- **Rubric:**
  1. *Correct Interpretation* (50%) — Is the provided metric/log data correctly read and connected?
  2. *Correct Diagnosis* (50%) — Is the likely root cause correctly identified from the evidence alone?

---

### Activity 12.8 — Project: Full Observability Stack
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 12.5 | **Prerequisites:** Activity 12.7
- **Evidence Required:** Screenshot + Presentation/Slide Deck
- **Review & Collaboration:** None
- **Prompt:** Fellow adds full observability (metrics, alarms, centralized logging, dashboard) to their Milestone 9/10 infrastructure (provisioned via Terraform where possible — connecting observability config back to IaC), and produces a short slide deck explaining what's monitored, what alerts exist, and what each alarm threshold means in business terms (e.g., "this fires if the site would likely feel slow to a real user"). **Tear down all resources when done.**
- **Rubric:**
  1. *Observability Completeness* (35%) — Are metrics, alarms, logs, and a dashboard all correctly present?
  2. *IaC Integration* (25%) — Is monitoring configuration itself managed via Terraform where reasonable?
  3. *Business-Relevant Thresholds* (25%) — Are alarm thresholds meaningfully tied to real user impact, not arbitrary numbers?
  4. *Cost Discipline* (15%) — Is teardown correctly completed?

---

### Activity 12.9 — Brand: Building Systems That Tell You When They're Broken
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 12.8 | **Prerequisites:** Activity 12.8
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video on the Full Observability Stack project — what's monitored, why those specific alarms/thresholds were chosen, and a demo of an alert actually firing.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates a live alert?
  3. *Threshold Reasoning* (25%) — Correctly explains why specific thresholds were chosen?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 12.10 — Mock Interview: Observability Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 12.7, Activity 12.8
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain the three pillars of observability, why centralized logging matters, walk through their Full Observability Stack's alarm thresholds and reasoning, and discuss the Activity 12.7 diagnosis-from-dashboard scenario.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 12 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 12.1 | Why "It's Running" Isn't Good Enough | Reflection | Beginner | 5 | Required |
| 12.2 | CloudWatch Metrics Fundamentals | Workshop | Beginner | 15 | Required |
| 12.3 | CloudWatch Alarms & Notifications | Workshop | Intermediate | 20 | Required |
| 12.4 | Centralized Logging with CloudWatch Logs | Workshop | Intermediate | 20 | Required |
| 12.5 | Dashboards: Visualizing System Health | Workshop | Intermediate | 15 | Required |
| 12.6 | Case Study: The Outage Nobody Noticed | Case Study | Intermediate | 15 | Required |
| 12.7 | Debug Challenge: Diagnose from the Dashboard | Debug Challenge | Advanced | 20 | Required |
| 12.8 | Project: Full Observability Stack | Project | Advanced | 25 | Required |
| 12.9 | Building Systems That Tell You When They're Broken (Brand) | Blog Post | Advanced | 15 | Required |
| 12.10 | Mock Interview: Observability Check | Mock Interview | Advanced | 15 | Required |

**Milestone 12 total (if all completed):** 165 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–12)
- Milestones 1–11: 1,780 pts
- Milestone 12: 165 pts
- **Cumulative possible so far:** 1,945 pts

---

## What's Next: Milestone 13 Preview
**Cloud Security & IAM Best Practices** — least privilege revisited in depth, encryption at rest/in transit, and Security Groups/networking revisited specifically through a security-hardening lens, before the track's final Cost Optimization and Capstone Milestones. Say the word when ready.
