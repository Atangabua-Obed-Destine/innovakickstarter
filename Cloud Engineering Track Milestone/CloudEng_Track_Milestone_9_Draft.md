# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 9: Infrastructure as Code with Terraform

**Status:** Draft for review
**Unlocks:** After Milestone 8 is fully completed
**Theme:** The shift from clicking through the AWS Console (every prior Milestone) to defining infrastructure in code — reproducible, version-controlled, and reviewable via Git PRs. This is where the track's Collaborate pillar comes fully alive: infrastructure code review is exactly how real cloud teams operate.
**Target fellow:** AWS-infrastructure-fluent and containerization-comfortable; has never used an IaC tool.

---

### Activity 9.1 — Why Infrastructure as Code?
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 8.8
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on how many times across Milestones 4–7 they manually clicked through the AWS Console to build (and rebuild, and tear down, and rebuild again) similar infrastructure. Fellow researches the core IaC value proposition — reproducibility, version control, peer review, disaster recovery (rebuild everything from code if it's ever lost) — and estimates how much manual clicking a single Terraform apply could have replaced in their own prior work.
- **Rubric:**
  1. *Understanding* (60%) — Is the IaC value proposition correctly explained?
  2. *Applied Reflection* (40%) — Is the personal estimate/reflection genuine and specific?

---

### Activity 9.2 — Terraform Fundamentals: Providers & Resources
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 9.1 | **Prerequisites:** Activity 9.1
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Install Terraform, configure the AWS provider, and learn `terraform init`, `plan`, `apply`, and `destroy`. Fellow writes their first Terraform configuration defining a single S3 bucket, runs the full workflow (init → plan → apply), verifies the bucket exists in the AWS Console, then destroys it via `terraform destroy` — establishing the "destroy is the cleanup command" habit from day one of IaC.
- **Rubric:**
  1. *Correct Workflow* (60%) — Is the full init/plan/apply/destroy cycle correctly executed?
  2. *Configuration Correctness* (40%) — Is the Terraform code syntactically correct and does it produce the intended resource?

---

### Activity 9.3 — Variables, Outputs & State
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.2 | **Prerequisites:** Activity 9.2
- **Evidence Required:** GitHub Repository + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn variables (`variable` blocks, `.tfvars` files — never hardcoding values), outputs (exposing useful values after apply, like a resource's ID or URL), and — critically — Terraform state (what it is, why it matters, and why `terraform.tfstate` should never be committed to Git or edited by hand). Fellow refactors Activity 9.2's configuration to use variables for the bucket name and region, adds an output for the bucket ARN, and writes a short explanation of what would go wrong if two people ran `terraform apply` against the same infrastructure using local state files independently (motivating remote state, covered next).
- **Rubric:**
  1. *Correct Variable/Output Usage* (50%) — Are variables and outputs correctly implemented?
  2. *State Understanding* (50%) — Is the local-state-conflict risk correctly explained?

---

### Activity 9.4 — Remote State & Team Collaboration
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.3 | **Prerequisites:** Activity 9.3
- **Evidence Required:** Screenshot + GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Learn remote state (storing `.tfstate` in S3, with DynamoDB for state locking to prevent concurrent-apply conflicts — directly resolving Activity 9.3's identified risk, and a nice callback to Milestone 6's S3/DynamoDB knowledge). Fellow configures an S3 backend with DynamoDB locking for their Terraform configuration and demonstrates it working (state file visible in the S3 bucket, lock table showing entries during an apply).
- **Rubric:**
  1. *Correct Remote Backend Setup* (60%) — Is the S3 + DynamoDB remote state backend correctly configured?
  2. *Demonstrated Locking* (40%) — Is the state locking behavior correctly shown?

---

### Activity 9.5 — Building a VPC with Terraform
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.4 | **Prerequisites:** Activity 9.4
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Fellow rewrites their Milestone 7 custom VPC (subnets, IGW, route tables, Security Groups) entirely as Terraform code, applies it to create a real VPC, verifies it matches their manual Milestone 7 build, then destroys it — proving they can now reproduce in minutes via code what previously took an hour of Console clicking.
- **Rubric:**
  1. *Correct VPC Configuration* (50%) — Does the Terraform code correctly produce the intended network architecture?
  2. *Code Organization* (30%) — Is the configuration reasonably organized (not one giant unstructured file)?
  3. *Destroy Verification* (20%) — Is complete teardown via `terraform destroy` demonstrated?

---

### Activity 9.6 — Modules: Reusable Infrastructure
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 9.5 | **Prerequisites:** Activity 9.5
- **Evidence Required:** GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Learn Terraform modules — packaging reusable, parameterized infrastructure components. Fellow refactors their Activity 9.5 VPC configuration into a reusable module (accepting variables like CIDR range and AZ count), then calls that module twice with different inputs (e.g., a "dev" and "staging" VPC) to prove genuine reusability, without duplicating code.
- **Rubric:**
  1. *Correct Module Structure* (50%) — Is the module correctly structured with sensible inputs/outputs?
  2. *Genuine Reusability* (50%) — Are two distinct VPCs correctly produced from the same module with different inputs?

---

### Activity 9.7 — Collaborate: Infrastructure Pull Request Review
- **Activity Type:** Code Review
- **Pillar:** Collaborate
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.6
- **Evidence Required:** GitHub Commit/PR + Written Submission
- **Review & Collaboration:** ✅ Requires Peer Review, ✅ Collaborative Activity
- **Prompt:** Fellow opens a real pull request proposing a change to their Terraform module from Activity 9.6 (e.g., adding a new configurable parameter), and a peer (or mentor-provided sample PR) reviews it, leaving substantive feedback — checking for hardcoded values, missing variable descriptions, or overly broad Security Group rules (a genuine, common Terraform PR review focus). Fellow addresses the feedback and merges. This directly echoes real how infrastructure changes get reviewed at real companies before touching production.
- **Rubric:**
  1. *PR Quality* (35%) — Clear description of the proposed infrastructure change?
  2. *Review Substance* (35%) — Is the feedback given (by the fellow, reviewing someone else's PR if paired) specific and technically sound?
  3. *Responsiveness* (30%) — Was feedback genuinely incorporated before merging?

---

### Activity 9.8 — Debug Challenge: The Terraform Drift
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 9.5
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn about "configuration drift" — when someone manually changes a resource in the Console that Terraform is managing, causing the real infrastructure and the Terraform state to disagree. Fellow deliberately creates drift (e.g., manually changes a tag on a Terraform-managed resource via the Console), runs `terraform plan` to observe how Terraform detects and reports the drift, and explains the two ways to resolve it (accept the manual change by updating the code, or let Terraform revert it on the next apply) and when each approach is appropriate.
- **Rubric:**
  1. *Drift Correctly Demonstrated* (40%) — Is drift correctly created and detected via `terraform plan`?
  2. *Resolution Understanding* (40%) — Are both resolution paths correctly explained?
  3. *Judgment* (20%) — Is the "when to choose which" reasoning sound?

---

### Activity 9.9 — Brand: Infrastructure as Code, Explained
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 9.6 | **Prerequisites:** Activity 9.6, Activity 9.8
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video walking through the journey from Milestone 7's manual VPC clicking to this Milestone's fully modular Terraform-defined VPC — a genuinely compelling "before/after" narrative that resonates strongly with hiring managers who value IaC skills.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates the Terraform workflow?
  3. *Narrative Arc* (25%) — Does it genuinely convey the manual-to-automated journey?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 9.10 — Mock Interview: IaC & Terraform Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.7, Activity 9.8
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain what Terraform state is and why remote state with locking matters, walk through their module design from Activity 9.6, discuss configuration drift and how to resolve it, and reflect on the PR review process from Activity 9.7.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 9 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 9.1 | Why Infrastructure as Code? | Reflection | Beginner | 5 | Required |
| 9.2 | Terraform Fundamentals: Providers & Resources | Workshop | Beginner | 15 | Required |
| 9.3 | Variables, Outputs & State | Workshop | Intermediate | 20 | Required |
| 9.4 | Remote State & Team Collaboration | Workshop | Advanced | 20 | Required |
| 9.5 | Building a VPC with Terraform | Project | Advanced | 25 | Required |
| 9.6 | Modules: Reusable Infrastructure | Workshop | Advanced | 20 | Required |
| 9.7 | Collaborate: Infrastructure Pull Request Review | Code Review | Intermediate | 15 | Required |
| 9.8 | Debug Challenge: The Terraform Drift | Debug Challenge | Advanced | 20 | Required |
| 9.9 | Infrastructure as Code, Explained (Brand) | Blog Post | Advanced | 15 | Required |
| 9.10 | Mock Interview: IaC & Terraform Check | Mock Interview | Advanced | 15 | Required |

**Milestone 9 total (if all completed):** 170 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–9)
- Milestones 1–8: 1,275 pts
- Milestone 9: 170 pts
- **Cumulative possible so far:** 1,445 pts

---

## Operational Note
Activity 9.7 (Infrastructure PR Review) needs the same peer-pairing infrastructure flagged in the other three tracks — worth confirming this is being solved once at the platform level, since it's now needed across all four tracks.

---

## What's Next: Milestone 10 Preview
**CI/CD Pipelines with GitHub Actions** — the dedicated pipeline Milestone, automating build/test/deploy so fellows stop manually running `docker build` and `terraform apply` by hand and start triggering deployments through Git pushes. Say the word when ready.
