# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 6: Storage & Databases in the Cloud

**Status:** Draft for review
**Unlocks:** After Milestone 5 is fully completed
**Theme:** Moving from "where does compute live" to "where does data live." S3 deep dive (versioning, lifecycle policies), EBS volumes, RDS (managed relational databases), and an introduction to DynamoDB (NoSQL) — plus the critical discipline of backups and data durability.
**Target fellow:** Comfortable with EC2/ALB/ASG; has only touched S3 at a basic level (Milestone 4).

---

### Activity 6.1 — Storage Types: Object, Block & File
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.7
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the three storage paradigms AWS offers: object storage (S3 — files with metadata, accessed via API), block storage (EBS — raw disk volumes attached to an instance, like a virtual hard drive), and file storage (EFS — shared filesystems mountable by multiple instances, briefly introduced). Fellow explains which type would be appropriate for 4 different scenarios (e.g., "user-uploaded profile photos" → S3; "the OS disk for an EC2 instance" → EBS) with reasoning.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Are the three storage types correctly distinguished?
  2. *Applied Reasoning* (40%) — Are scenario-to-storage-type matches correctly reasoned?

---

### Activity 6.2 — S3 Deep Dive: Versioning & Lifecycle Policies
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.1 | **Prerequisites:** Activity 6.1
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn S3 versioning (protecting against accidental overwrites/deletions — a genuine real-world lifesaver) and lifecycle policies (automatically transitioning old objects to cheaper storage classes, or deleting them after a set time — directly tied to cost discipline). Fellow enables versioning on a bucket, uploads a file, overwrites it, and demonstrates recovering the original version. Separately, fellow configures a lifecycle policy that would transition objects older than 30 days to a cheaper storage class, explaining the cost-saving logic.
- **Rubric:**
  1. *Versioning Demonstrated* (40%) — Is version recovery correctly demonstrated?
  2. *Lifecycle Policy Correctness* (35%) — Is the lifecycle rule correctly configured?
  3. *Cost Reasoning* (25%) — Is the cost-saving logic correctly explained?

---

### Activity 6.3 — EBS Volumes & Snapshots
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.1
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn EBS volumes (persistent block storage attached to EC2 instances — surviving instance termination if configured correctly, unlike instance store) and EBS Snapshots (point-in-time backups). Fellow launches an EC2 instance, attaches an additional EBS volume, writes a test file to it, takes a snapshot, then deliberately deletes the file and restores it from the snapshot by creating a new volume — demonstrating the backup/restore cycle. **Terminate everything, including volumes and snapshots, when done** (snapshots incur ongoing storage charges if left behind — a genuine common cost trap).
- **Rubric:**
  1. *Correct EBS Setup* (40%) — Is the volume correctly attached and used?
  2. *Backup/Restore Demonstrated* (40%) — Is the snapshot restore correctly demonstrated?
  3. *Full Cleanup* (20%) — Are the volume AND snapshot both cleaned up (the commonly-forgotten part)?

---

### Activity 6.4 — RDS Fundamentals: Managed Relational Databases
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.3 | **Prerequisites:** Activity 6.3, Activity 3.5
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn RDS — a managed database service (AWS handles patching, backups, failover) — and why teams use it instead of self-managing a database on an EC2 instance. Fellow launches a free-tier RDS MySQL instance **in a private subnet** (applying Milestone 3's public/private reasoning — a database should never be directly internet-facing), connects to it from an EC2 instance in the same VPC (not from their local machine directly), creates a simple table, and inserts/queries test data. **Delete the RDS instance when done** (RDS is one of the most common sources of unexpected Free Tier overage if forgotten).
- **Rubric:**
  1. *Correct Network Placement* (30%) — Is RDS correctly placed in a private subnet, not publicly accessible?
  2. *Correct Connection & Usage* (40%) — Is the EC2-to-RDS connection and basic SQL usage correctly demonstrated?
  3. *Cleanup* (30%) — Is the RDS instance correctly deleted afterward (with explicit confirmation, since AWS prompts extra warnings here for good reason)?

---

### Activity 6.5 — Intro to DynamoDB: NoSQL in the Cloud
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.4
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn DynamoDB's core model (partition keys, sort keys, schema-less items) versus RDS's relational model, and when each fits better (RDS: complex relationships, transactions, structured reporting; DynamoDB: massive scale, simple access patterns, low-latency key-value lookups). Fellow creates a small DynamoDB table (free tier), inserts a few items via the console, and writes a comparison explaining which of Nkwen Traders' data (from the Data Science track, if familiar — or a similar generic scenario) would fit better in RDS vs. DynamoDB and why.
- **Rubric:**
  1. *Conceptual Accuracy* (50%) — Is the RDS vs. DynamoDB distinction correctly explained?
  2. *Applied Reasoning* (50%) — Is the scenario-based comparison well-reasoned?

---

### Activity 6.6 — Case Study: The Missing Backup
- **Activity Type:** Case Study
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.2, Activity 6.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches one real, publicly documented case of a company or individual losing significant data due to a missing or misconfigured backup (many well-documented cases exist across S3 deletions, database failures, and disk crashes) and writes a case study covering what backup strategy (versioning, snapshots, automated RDS backups) would have prevented the loss, connecting directly to the specific mechanisms learned in this Milestone.
- **Rubric:**
  1. *Case Understanding* (50%) — Is the real incident accurately summarized?
  2. *Applied Prevention* (50%) — Is the connection to specific AWS backup mechanisms correct and well-reasoned?

---

### Activity 6.7 — Project: Full-Stack Data Layer
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.4 | **Prerequisites:** Activity 6.4, Activity 6.6
- **Evidence Required:** Screenshot + Written Submission + GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Combine this Milestone's skills into one architecture: an EC2 instance (public subnet) running a simple app that reads/writes to an RDS database (private subnet), with automated RDS backups enabled and an S3 bucket (versioned) used for storing application file uploads. Fellow documents the full architecture, demonstrates it working end-to-end, and — critically — **fully tears everything down** with a documented teardown checklist proving no billable resources remain.
- **Rubric:**
  1. *Architectural Correctness* (30%) — Is the network placement (public/private) and service integration correct?
  2. *Functional Demonstration* (30%) — Does the full data flow work end-to-end?
  3. *Backup Configuration* (20%) — Are RDS backups and S3 versioning correctly enabled?
  4. *Complete Teardown* (20%) — Is a genuinely complete, documented teardown provided?

---

### Activity 6.8 — Brand: Choosing the Right Data Store
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.7 | **Prerequisites:** Activity 6.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video explaining how to choose between S3, EBS, RDS, and DynamoDB for a given use case, using the Full-Stack Data Layer project as a concrete worked example throughout.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality?
  3. *Decision Framework Clarity* (25%) — Is the storage-choice framework genuinely useful and correct?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 6.9 — Mock Interview: Storage & Database Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.6, Activity 6.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 72/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain object vs. block vs. file storage, when to choose RDS vs. DynamoDB, walk through the Full-Stack Data Layer's architecture and backup strategy, and discuss the Activity 6.6 case study's lessons.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 6 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 6.1 | Storage Types: Object, Block & File | Technical Research | Beginner | 10 | Required |
| 6.2 | S3 Deep Dive: Versioning & Lifecycle Policies | Workshop | Intermediate | 20 | Required |
| 6.3 | EBS Volumes & Snapshots | Workshop | Intermediate | 20 | Required |
| 6.4 | RDS Fundamentals | Workshop | Advanced | 25 | Required |
| 6.5 | Intro to DynamoDB | Technical Research | Intermediate | 15 | Required |
| 6.6 | Case Study: The Missing Backup | Case Study | Intermediate | 15 | Required |
| 6.7 | Project: Full-Stack Data Layer | Project | Advanced | 30 | Required |
| 6.8 | Choosing the Right Data Store (Brand) | Blog Post | Advanced | 15 | Required |
| 6.9 | Mock Interview: Storage & Database Check | Mock Interview | Advanced | 15 | Required |

**Milestone 6 total (if all completed):** 165 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–6)
- Milestone 1: 95 pts
- Milestone 2: 115 pts
- Milestone 3: 110 pts
- Milestone 4: 145 pts
- Milestone 5: 160 pts
- Milestone 6: 165 pts
- **Cumulative possible so far:** 950 pts

---

## Cost Discipline Note
RDS and EBS Snapshots are flagged explicitly in this Milestone's rubrics as common sources of forgotten, ongoing charges (unlike EC2, which fellows have practiced terminating repeatedly, RDS deletion and snapshot cleanup are newer habits). Worth the platform considering an automated end-of-Milestone reminder ("did you delete your RDS instance?") given how genuinely common this specific mistake is in real practice.

---

## What's Next: Milestone 7 Preview
**Cloud Networking: VPCs** — subnets, route tables, security groups, and NACLs, formalizing everything Milestone 3's networking foundations and this Milestone's public/private subnet practice have been building toward. Fellows will design and build a real custom VPC from scratch rather than relying on the default one. Say the word when ready.
