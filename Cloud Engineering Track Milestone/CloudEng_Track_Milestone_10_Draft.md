# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 10: CI/CD Pipelines with GitHub Actions

**Status:** Draft for review
**Unlocks:** After Milestone 9 is fully completed
**Theme:** The dedicated pipeline Milestone. Deployments stop being manual (`docker build`, `docker push`, `terraform apply` run by hand) and start being automated, triggered by a Git push. This is where Docker (Milestone 8) and Terraform (Milestone 9) get wired together into a real automated workflow.
**Target fellow:** Containerization- and Terraform-fluent; has never built a CI/CD pipeline.

---

### Activity 10.1 — Why Automate Deployment?
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.9
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on how many manual steps Milestone 8's ECR push and Milestone 9's `terraform apply` each required, and how error-prone and slow manual deployment is at scale (forgotten steps, deploying the wrong version, inconsistent process between team members). Fellow researches the core CI/CD value proposition (Continuous Integration: automatically test every change; Continuous Deployment: automatically ship passing changes) and explains it in their own words.
- **Rubric:**
  1. *Understanding* (60%) — Is the CI/CD value proposition correctly explained?
  2. *Applied Reflection* (40%) — Is the connection to their own prior manual-step experience genuine?

---

### Activity 10.2 — GitHub Actions Fundamentals
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 10.1 | **Prerequisites:** Activity 10.1
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn GitHub Actions core concepts: workflows, jobs, steps, triggers (`on: push`, `on: pull_request`), and the Actions marketplace. Fellow writes a simple workflow YAML file that triggers on every push to `main` and runs a trivial step (e.g., `echo "Hello from CI"`), verifies it runs successfully in the Actions tab, and screenshots the green checkmark.
- **Rubric:**
  1. *Correct Workflow Syntax* (60%) — Is the YAML correctly structured and triggering as intended?
  2. *Verified Execution* (40%) — Is successful execution correctly demonstrated?

---

### Activity 10.3 — Continuous Integration: Automated Testing
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.2 | **Prerequisites:** Activity 10.2
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Extend the workflow to run real automated tests against the Milestone 8 containerized application (or a simple test suite fellow writes if none exists) on every push AND every pull request — the "CI" in CI/CD. Fellow deliberately introduces a failing test, pushes it, and screenshots the workflow correctly failing and blocking (demonstrating CI actually catches problems, not just runs silently).
- **Rubric:**
  1. *Correct Test Integration* (50%) — Are tests correctly run as part of the workflow?
  2. *Failure Detection Demonstrated* (50%) — Is a genuine test failure correctly caught and shown blocking the pipeline?

---

### Activity 10.4 — Building & Pushing Docker Images in CI
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.3 | **Prerequisites:** Activity 10.3
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Extend the workflow to build the Docker image and push it to ECR (Milestone 8) automatically on a successful push to `main` — using GitHub Secrets to securely store AWS credentials (never hardcoded in the workflow file, echoing Milestone 8's environment-variable security lessons). Fellow verifies a new image tag appears in ECR after each successful pipeline run.
- **Rubric:**
  1. *Correct Build & Push* (50%) — Does the workflow correctly build and push the image to ECR?
  2. *Secure Credential Handling* (30%) — Are AWS credentials correctly stored as GitHub Secrets, never hardcoded?
  3. *Verified Automation* (20%) — Is the new image appearing in ECR after a push correctly demonstrated?

---

### Activity 10.5 — Continuous Deployment: Terraform in the Pipeline
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.4 | **Prerequisites:** Activity 10.4, Activity 9.4
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Extend the workflow to run `terraform plan` automatically on every pull request (so reviewers see exactly what infrastructure will change before approving — a genuine best practice) and `terraform apply` automatically on merge to `main`, using the remote state backend from Milestone 9. Fellow demonstrates a full cycle: open a PR with an infra change, see the plan output posted automatically, merge, and confirm the change applied.
- **Rubric:**
  1. *Correct Plan-on-PR* (35%) — Does the plan step correctly run and report on pull requests?
  2. *Correct Apply-on-Merge* (35%) — Does apply correctly trigger only on merge to `main`?
  3. *State Safety* (30%) — Is remote state correctly used, avoiding conflicts?

---

### Activity 10.6 — Case Study: The Broken Pipeline
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 10.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches a real, publicly documented incident where a CI/CD pipeline itself caused an outage (e.g., a bad deployment that skipped testing, a pipeline that deployed to production instead of staging, credentials leaked through a misconfigured workflow) and writes a case study on what safeguard (the kind built in this Milestone — required tests passing, plan-before-apply, secrets management) would have prevented it.
- **Rubric:**
  1. *Case Understanding* (50%) — Is the real incident accurately summarized?
  2. *Applied Prevention* (50%) — Is the connection to specific CI/CD safeguards correct and well-reasoned?

---

### Activity 10.7 — Debug Challenge: The Pipeline That Won't Pass
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.5 | **Prerequisites:** Activity 10.5
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a broken GitHub Actions workflow (platform-provided: a missing secret reference, an incorrect job dependency causing steps to run out of order, a permissions issue preventing ECR push) and must read the Actions logs, diagnose the root cause, and fix it — the real, common experience of "why is my pipeline red" debugging.
- **Rubric:**
  1. *Correct Log Reading* (30%) — Are the Actions logs correctly interpreted to find the issue?
  2. *Correct Diagnosis* (35%) — Is the root cause correctly identified?
  3. *Correct Fix* (35%) — Is the pipeline genuinely passing afterward?

---

### Activity 10.8 — Project: Full CI/CD Pipeline
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.5 | **Prerequisites:** Activity 10.6, Activity 10.7
- **Evidence Required:** GitHub Repository + Presentation/Slide Deck
- **Review & Collaboration:** None
- **Prompt:** Fellow assembles the complete pipeline into one polished repository: on every PR, tests run and a Terraform plan is posted; on merge to `main`, the Docker image builds and pushes to ECR, and infrastructure changes apply automatically. Fellow produces a short slide deck (5–7 slides) diagramming the full pipeline flow, and demonstrates one complete end-to-end cycle from code change to live deployment.
- **Rubric:**
  1. *Pipeline Completeness* (35%) — Does the full CI/CD flow work correctly end-to-end?
  2. *Security & Best Practices* (25%) — Are secrets, plan-before-apply, and test gates correctly implemented?
  3. *Pipeline Diagram Quality* (25%) — Is the slide deck clear and accurately represents the flow?
  4. *Live Demonstration* (15%) — Is a genuine end-to-end deployment cycle shown?

---

### Activity 10.9 — Brand: From Manual Deploys to Automated Pipelines
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 10.8 | **Prerequisites:** Activity 10.8
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video walking through the Full CI/CD Pipeline project — the journey from manually running `docker build`/`terraform apply` to pushing a Git commit and watching the whole thing deploy automatically. Use the pipeline diagram from Activity 10.8 as visual support.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates the pipeline live?
  3. *Narrative Arc* (25%) — Does it genuinely convey the manual-to-automated journey?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 10.10 — Mock Interview: CI/CD Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 10.7, Activity 10.8
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain the difference between CI and CD, why secrets should never be hardcoded in workflows, walk through their Full CI/CD Pipeline architecture, and discuss the Activity 10.7 debugging process.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 10 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 10.1 | Why Automate Deployment? | Reflection | Beginner | 5 | Required |
| 10.2 | GitHub Actions Fundamentals | Workshop | Beginner | 15 | Required |
| 10.3 | Continuous Integration: Automated Testing | Workshop | Intermediate | 20 | Required |
| 10.4 | Building & Pushing Docker Images in CI | Workshop | Advanced | 20 | Required |
| 10.5 | Continuous Deployment: Terraform in the Pipeline | Workshop | Advanced | 25 | Required |
| 10.6 | Case Study: The Broken Pipeline | Case Study | Intermediate | 15 | Required |
| 10.7 | Debug Challenge: The Pipeline That Won't Pass | Debug Challenge | Advanced | 20 | Required |
| 10.8 | Project: Full CI/CD Pipeline | Project | Advanced | 30 | Required |
| 10.9 | From Manual Deploys to Automated Pipelines (Brand) | Blog Post | Advanced | 15 | Required |
| 10.10 | Mock Interview: CI/CD Check | Mock Interview | Advanced | 15 | Required |

**Milestone 10 total (if all completed):** 180 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–10)
- Milestones 1–9: 1,445 pts
- Milestone 10: 180 pts
- **Cumulative possible so far:** 1,625 pts

---

## This Closes the Modern Deployment Arc
Milestones 8–10 (Docker, Terraform, CI/CD) form the complete "how modern teams actually ship software" arc, sitting directly on top of the AWS infrastructure fluency built in Milestones 4–7. From here, the track shifts toward orchestration at scale, observability, security hardening, and cost optimization — the operational maturity layer.

---

## What's Next: Milestone 11 Preview
**Kubernetes Introduction** — core concepts (pods, deployments, services) and deploying to a managed Kubernetes service (EKS), kept at foundations-depth per your earlier direction, with deeper orchestration mastery reserved for a later specialization branch. Say the word when ready.
