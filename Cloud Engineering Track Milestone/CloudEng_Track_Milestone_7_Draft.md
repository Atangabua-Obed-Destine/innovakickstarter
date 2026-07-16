# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 7: Cloud Networking — VPCs

**Status:** Draft for review
**Unlocks:** After Milestone 6 is fully completed
**Theme:** Formalizing everything Milestone 3's networking foundations and Milestone 6's public/private subnet practice have been building toward. Fellows design and build a real custom VPC from scratch — subnets, route tables, security groups, and NACLs — rather than relying on AWS's default VPC as they have been until now.
**Target fellow:** Has used the default VPC implicitly through Milestones 4–6; understands CIDR notation and public/private separation conceptually from Milestone 3.

---

### Activity 7.1 — Why Build a Custom VPC?
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.7
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on every prior Milestone's use of AWS's default VPC — it worked, but was invisible and unconfigurable. Fellow researches why real production environments almost always use custom VPCs (control over IP ranges, deliberate subnet design, avoiding conflicts when connecting to other networks like a VPN or another VPC) and explains the risk of just "always using the default."
- **Rubric:**
  1. *Understanding* (70%) — Is the case for custom VPCs correctly reasoned?
  2. *Risk Awareness* (30%) — Is the "default VPC everywhere" risk correctly identified?

---

### Activity 7.2 — VPC & Subnet Design
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.1 | **Prerequisites:** Activity 7.1
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Applying Milestone 3's CIDR skills for real: fellow designs (on paper/diagram first) a VPC with CIDR `10.0.0.0/16`, containing 2 public subnets and 2 private subnets across 2 Availability Zones (4 subnets total — the standard production pattern for high availability), then creates all of it in the AWS Console, screenshotting the final VPC/subnet layout and explaining their CIDR allocation choices for each subnet.
- **Rubric:**
  1. *Correct CIDR Design* (40%) — Are subnet CIDR blocks correctly sized and non-overlapping within the VPC?
  2. *Correct AWS Implementation* (40%) — Is the VPC/subnet structure correctly created in AWS matching the design?
  3. *Design Reasoning* (20%) — Is the allocation choice clearly explained?

---

### Activity 7.3 — Internet Gateways & Route Tables
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.2 | **Prerequisites:** Activity 7.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn Internet Gateways (IGWs — the VPC's connection to the internet) and route tables (defining where traffic goes based on destination). Fellow attaches an IGW to their Milestone 7.2 VPC, configures the public subnets' route table with a route to the IGW (making them genuinely "public"), and verifies the private subnets correctly have NO such route — then explains, in their own words, exactly what makes a subnet "public" in AWS (it's the route table, not a label).
- **Rubric:**
  1. *Correct Configuration* (50%) — Are IGW and route tables correctly configured?
  2. *Conceptual Accuracy* (50%) — Is the "route table defines public/private, not a checkbox" explanation correct?

---

### Activity 7.4 — NAT Gateways: Private Subnets Reaching the Internet
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.3 | **Prerequisites:** Activity 7.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the seeming paradox: private subnet resources sometimes need *outbound* internet access (e.g., a private EC2 instance downloading OS updates) without being *inbound* reachable. Learn NAT Gateways as the solution. Fellow deploys a NAT Gateway in a public subnet, updates the private subnet's route table to send outbound traffic through it, and verifies a private EC2 instance can reach the internet outbound (e.g., successfully run `sudo apt update`) while confirming it's still not reachable from outside. **NAT Gateways have an hourly cost — delete it immediately after this activity, a genuine and common Free Tier gotcha.**
- **Rubric:**
  1. *Correct NAT Configuration* (50%) — Is the NAT Gateway correctly deployed and routed?
  2. *Outbound-Only Behavior Verified* (30%) — Is the outbound-yes/inbound-no distinction correctly demonstrated?
  3. *Cost Discipline* (20%) — Is the NAT Gateway promptly deleted (with explicit acknowledgment of its cost)?

---

### Activity 7.5 — Security Groups vs. NACLs
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 7.2
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the important distinction between Security Groups (stateful, instance-level, allow-rules only) and Network ACLs (stateless, subnet-level, can explicitly deny). Fellow writes a comparison table and explains a scenario where a NACL is genuinely useful even though Security Groups already exist (e.g., blocking a specific malicious IP range at the subnet level as an extra layer — defense in depth, echoing the layered-security thinking from the Cybersecurity track if a fellow has taken it).
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the stateful/stateless, instance/subnet distinction correctly explained?
  2. *Applied Scenario* (40%) — Is the "when NACLs add value" scenario genuinely sound?

---

### Activity 7.6 — Debug Challenge: The Silent Network
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.4 | **Prerequisites:** Activity 7.4, Activity 7.5
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a described scenario: a newly launched EC2 instance in a "public" subnet still isn't reachable from the internet despite having a public IP. Fellow must reason through the full checklist (in the correct order, since each layer can independently block traffic): is there a route to an IGW? Is the Security Group allowing the port? Is the NACL allowing it (in both directions — a commonly-missed detail since NACLs are stateless)? Is the OS-level firewall (e.g., `ufw`/`iptables`) blocking it too? Fellow proposes the systematic diagnostic order and the most likely culprit(s).
- **Rubric:**
  1. *Systematic Diagnostic Order* (40%) — Is a logical, layer-by-layer checklist followed?
  2. *Correct Culprit Identification* (40%) — Are the most likely causes correctly identified given the scenario?
  3. *NACL Statelessness Awareness* (20%) — Is the "NACLs need both-direction rules" nuance correctly applied?

---

### Activity 7.7 — Project: Production-Grade Custom VPC
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.4 | **Prerequisites:** Activity 7.4, Activity 7.6
- **Evidence Required:** Screenshot + Written Submission + Presentation/Slide Deck
- **Review & Collaboration:** None
- **Prompt:** Fellow rebuilds the Milestone 5 Highly Available Web Application and Milestone 6 Full-Stack Data Layer projects **inside their own custom VPC** from this Milestone (rather than the default VPC used previously) — web tier in public subnets behind an ALB, application/database tier in private subnets, correctly routed and secured. Fellow produces a short architecture slide deck (5–8 slides) documenting the full network design, then **fully tears down all resources**, documenting the teardown.
- **Rubric:**
  1. *Correct Custom VPC Architecture* (35%) — Is the full stack correctly deployed in the custom VPC with proper tier separation?
  2. *Security Configuration* (25%) — Are Security Groups/NACLs correctly and minimally scoped?
  3. *Architecture Presentation* (25%) — Is the slide deck clear, accurate, and professionally presentable?
  4. *Complete Teardown* (15%) — Is teardown genuinely complete and documented?

---

### Activity 7.8 — Brand: Designing My First Production Network
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 7.7 | **Prerequisites:** Activity 7.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video walking through the Production-Grade Custom VPC project — the design decisions, the CIDR planning, and the public/private tier separation, using the slide deck from Activity 7.7 as visual support in the video.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, uses visual aids effectively?
  3. *Technical Accuracy* (25%) — Correctly explains the network design?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 7.9 — Mock Interview: VPC & Networking Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.6, Activity 7.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain what makes a subnet public vs. private, Security Groups vs. NACLs, walk through the custom VPC architecture from Activity 7.7, and reason through the Activity 7.6 diagnostic scenario live.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 7 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 7.1 | Why Build a Custom VPC? | Reflection | Beginner | 5 | Required |
| 7.2 | VPC & Subnet Design | Workshop | Intermediate | 20 | Required |
| 7.3 | Internet Gateways & Route Tables | Workshop | Intermediate | 20 | Required |
| 7.4 | NAT Gateways | Workshop | Advanced | 20 | Required |
| 7.5 | Security Groups vs. NACLs | Technical Research | Intermediate | 15 | Required |
| 7.6 | Debug Challenge: The Silent Network | Debug Challenge | Advanced | 20 | Required |
| 7.7 | Project: Production-Grade Custom VPC | Project | Advanced | 30 | Required |
| 7.8 | Designing My First Production Network (Brand) | Blog Post | Advanced | 15 | Required |
| 7.9 | Mock Interview: VPC & Networking Check | Mock Interview | Advanced | 15 | Required |

**Milestone 7 total (if all completed):** 160 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–7)
- Milestones 1–6: 950 pts
- Milestone 7: 160 pts
- **Cumulative possible so far:** 1,110 pts

---

## Cost Discipline Note
**NAT Gateways are one of the single most common sources of unexpected AWS bills** among learners specifically because they're easy to forget (they don't look "running" the way an EC2 instance does) and bill hourly even when idle. Activity 7.4's rubric explicitly requires prompt deletion — worth the platform considering this the single highest-priority automated reminder across the entire track.

---

## This Closes the Core AWS Infrastructure Arc
Milestones 4–7 (AWS Fundamentals, Compute, Storage/Databases, Networking) form the complete "how do I actually run infrastructure on AWS" arc. From here, the track shifts into modern deployment practices — containers, IaC, and CI/CD — which is where most real cloud engineering work happens day-to-day.

---

## What's Next: Milestone 8 Preview
**Docker & Containerization Fundamentals** — images, containers, Dockerfiles, and registries. This is a genuinely pivotal Milestone: everything from Milestone 9 (Terraform) through 11 (Kubernetes) assumes fellows are comfortable containerizing an application. Say the word when ready.
