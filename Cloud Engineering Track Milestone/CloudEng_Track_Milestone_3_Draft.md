# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 3: Networking Fundamentals for Cloud

**Status:** Draft for review
**Unlocks:** After Milestone 2 is fully completed
**Theme:** Self-contained networking fundamentals — OSI/TCP-IP, IP addressing, ports/protocols, and DNS — framed specifically around what VPCs will later formalize (Milestone 7). Every concept here maps directly onto a real AWS networking construct fellows will configure soon.
**Target fellow:** Linux-fluent from Milestone 2; zero prior networking background assumed.

---

### Activity 3.1 — The OSI & TCP/IP Models
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Cost Discipline Gate)
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the 7-layer OSI model and the simpler 4-layer TCP/IP model. Fellow explains, in their own words, what happens at each relevant layer when they SSH into an EC2 instance (an example they've now personally done in Milestone 2) — connecting theory directly to lived experience.
- **Rubric:**
  1. *Model Accuracy* (60%) — Are layers and their purposes correctly described?
  2. *Applied Explanation* (40%) — Does the SSH walkthrough correctly map to the model?

---

### Activity 3.2 — IP Addressing & CIDR Notation
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.1 | **Prerequisites:** Activity 3.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn IPv4 addressing, public vs. private IP ranges, and CIDR notation in real depth this time — since CIDR blocks are exactly how VPCs and subnets get defined in AWS. Fellow solves 6–8 practice problems (e.g., "how many usable IPs does a /24 provide?", "does 10.0.1.0/24 fit entirely inside 10.0.0.0/16?") — the second question type specifically previews subnet-within-VPC reasoning coming in Milestone 7.
- **Rubric:**
  1. *Correctness* (80%) — Are practice problems answered correctly?
  2. *Reasoning Shown* (20%) — Is the CIDR-containment reasoning shown, not just final answers?

---

### Activity 3.3 — Ports, Protocols & Firewalls
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.2 | **Prerequisites:** Activity 3.2
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn common ports/protocols (HTTP/80, HTTPS/443, SSH/22, RDP/3389) and the general concept of a firewall — a rule set controlling what traffic is allowed in/out, which is exactly what an AWS Security Group is. Fellow builds a reference table matching 8 common ports to their protocol/use case, and separately writes firewall rules in plain English for a hypothetical web server (e.g., "allow HTTP/HTTPS from anywhere, allow SSH only from my home IP") — direct rehearsal for Security Group configuration in Milestone 7.
- **Rubric:**
  1. *Table Accuracy* (50%) — Are ports/protocols correctly matched?
  2. *Firewall Rule Logic* (50%) — Are the plain-English rules correctly scoped and reasonably secure (not overly permissive)?

---

### Activity 3.4 — DNS: How Domains Point to Servers
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.3
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn how DNS resolution works and common record types (A, CNAME, MX, TXT). Fellow launches a free-tier EC2 instance (remembering cost-discipline habits — terminate when done), notes its public IP, and uses `dig`/`nslookup` to look up records for 2–3 real domains, screenshotting the output with annotations. This previews Route 53 (AWS's DNS service) conceptually before it's formally introduced later in the track.
- **Rubric:**
  1. *Correct Tool Usage* (50%) — Are the commands run correctly with valid output?
  2. *Interpretation* (50%) — Is the output correctly explained?

---

### Activity 3.5 — Public vs. Private Networks: The Cloud Mental Model
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.2 | **Prerequisites:** Activity 3.2, Activity 3.3
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the conceptual distinction between resources that should be publicly reachable (a web server) versus resources that should stay private (a database, only reachable from the web server, never directly from the internet) — the exact reasoning behind public/private subnets in a VPC. Fellow diagrams (described in writing, or a simple hand-drawn/digital sketch) a basic 3-tier architecture for a small web app: a public-facing web server, a private application server, and a private database, explaining why each tier has the network exposure it does.
- **Rubric:**
  1. *Architectural Reasoning* (60%) — Is the public/private tier separation correctly reasoned?
  2. *Security Justification* (40%) — Is the "why keep the database private" reasoning sound?

---

### Activity 3.6 — Debug Challenge: Why Can't I Connect?
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.4 | **Prerequisites:** Activity 3.4, Activity 3.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a scenario (platform-provided, described in writing — no live infra needed yet) where a web server isn't reachable from the browser despite being "up," and must reason through the possible causes at each relevant layer: is the server process actually running and listening on the right port? Is a firewall/Security Group blocking the port? Is DNS pointing to the right IP? Fellow works through this like a real troubleshooting checklist and identifies the most likely cause(s) given the scenario details provided.
- **Rubric:**
  1. *Systematic Troubleshooting* (50%) — Is a logical, layer-by-layer diagnostic process followed?
  2. *Correct Diagnosis* (50%) — Is the most likely cause correctly identified from the given clues?

---

### Activity 3.7 — Brand: Networking Concepts for Cloud Engineers
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.6 | **Prerequisites:** Activity 3.6
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video explaining one concept from this Milestone in plain language — e.g., "Why Your Database Shouldn't Be Public" or "Troubleshooting 'Why Can't I Connect' Like an Engineer" — using the Activity 3.6 debugging process as a concrete walkthrough.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Accessibility* (15%) — Understandable to someone new to networking?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 3.8 — Mock Interview: Networking Fundamentals Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.5, Activity 3.6
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain CIDR notation, public vs. private subnet reasoning, how they'd troubleshoot a "can't connect" scenario, and walk through their 3-tier architecture sketch from Activity 3.5.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 3 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 3.1 | The OSI & TCP/IP Models | Technical Research | Beginner | 10 | Required |
| 3.2 | IP Addressing & CIDR Notation | Technical Research | Beginner | 15 | Required |
| 3.3 | Ports, Protocols & Firewalls | Technical Research | Beginner | 15 | Required |
| 3.4 | DNS: How Domains Point to Servers | Workshop | Beginner | 15 | Required |
| 3.5 | Public vs. Private Networks: The Cloud Mental Model | Technical Research | Intermediate | 15 | Required |
| 3.6 | Debug Challenge: Why Can't I Connect? | Debug Challenge | Intermediate | 20 | Required |
| 3.7 | Networking Concepts for Cloud Engineers (Brand) | Blog Post | Beginner | 10 | Required |
| 3.8 | Mock Interview: Networking Fundamentals Check | Mock Interview | Intermediate | 10 | Required |

**Milestone 3 total (if all completed):** 110 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–3)
- Milestone 1: 95 pts
- Milestone 2: 115 pts
- Milestone 3: 110 pts
- **Cumulative possible so far:** 320 pts

---

## This Sets Up the AWS Arc
Milestones 1–3 form the self-contained prerequisite foundation (cost discipline, Linux, networking). Everything from Milestone 4 onward is genuinely AWS-specific, and every networking concept here (CIDR, public/private separation, firewall rules, DNS) will be immediately revisited in concrete AWS form — Activity 3.2's CIDR practice becomes VPC/subnet design, Activity 3.3's firewall rules become Security Groups, Activity 3.5's 3-tier sketch becomes an actual VPC architecture, and Activity 3.4's DNS work becomes Route 53. Worth keeping this connective tissue explicit in the actual Milestone 7 content when we get there, since fellows will benefit from recognizing "oh, I already reasoned through this."

---

## What's Next: Milestone 4 Preview
**AWS Fundamentals & Core Services** — IAM in depth, regions/availability zones, EC2 revisited properly (not just launch/terminate), and S3 basics. This is where the track becomes concretely AWS-specific. Say the word when ready.
