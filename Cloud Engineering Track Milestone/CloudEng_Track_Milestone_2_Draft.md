# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 2: Linux & Command Line Fundamentals

**Status:** Draft for review
**Unlocks:** After Milestone 1 is fully completed (Cost Discipline Gate passed)
**Theme:** Self-contained Linux fundamentals from zero — filesystem, permissions, users, and shell scripting. Nearly all cloud work happens over SSH into Linux servers, so this is non-negotiable groundwork, taught independently rather than assuming any other track's Linux content.
**Target fellow:** Ethics/cost-gated; zero prior Linux or command-line experience assumed.

---

### Activity 2.1 — Why Linux Runs the Cloud
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Cost Discipline Gate)
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches why the overwhelming majority of cloud servers run Linux (cost, stability, ecosystem, scriptability) and writes a short reflection connecting this to their own EC2 experience from Milestone 1 — what OS was that instance running, and why that choice likely made sense.
- **Rubric:**
  1. *Understanding* (70%) — Correct reasoning about Linux's dominance in cloud infrastructure?
  2. *Personal Connection* (30%) — Ties back to their own Milestone 1 EC2 launch?

---

### Activity 2.2 — Filesystem Navigation
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.1 | **Prerequisites:** Activity 2.1
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn `pwd`, `ls`, `cd`, `mkdir`, `touch`, `cp`, `mv`, `rm`, and the Linux filesystem hierarchy (`/etc`, `/home`, `/var`, `/tmp`). Fellow launches a fresh free-tier EC2 instance (remembering Milestone 1's habits), SSHes in, creates a small folder structure for future cloud work (e.g., `cloud-notes` with subfolders for scripts and configs), navigates it, and screenshots the terminal history — then **terminates the instance** when done, per Cost Discipline Gate habits.
- **Rubric:**
  1. *Command Correctness* (60%) — Are commands correctly used to achieve the structure?
  2. *Cost Discipline* (40%) — Is the instance correctly terminated afterward (screenshot or note confirming cleanup)?

---

### Activity 2.3 — File Permissions & Ownership
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.2 | **Prerequisites:** Activity 2.2
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn `chmod`, `chown`, the `rwx` permission model, and numeric vs. symbolic notation — with particular attention to SSH key file permissions (`chmod 400`), since incorrect key permissions are one of the most common real-world "why can't I SSH in" problems. Fellow deliberately sets a private key file to overly permissive permissions, observes SSH refusing to use it, then corrects it and connects successfully — explaining what each permission bit means and why SSH enforces this.
- **Rubric:**
  1. *Correct Permission Manipulation* (60%) — Is `chmod` used correctly to fix the SSH key issue?
  2. *Conceptual Explanation* (40%) — Is the rwx model and SSH's enforcement reasoning correctly explained?

---

### Activity 2.4 — Users, Groups & Privilege Basics
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.3
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn user/group concepts, `sudo`, and the principle of least privilege. Fellow creates a new non-root user on an EC2 instance, adds them to an appropriate group, and demonstrates a `sudo`-required action failing for the new user versus succeeding for an authorized one — directly relevant since cloud servers are commonly configured with a default non-root user (e.g., `ec2-user`, `ubuntu`) rather than direct root login.
- **Rubric:**
  1. *Correct Setup* (60%) — Is the user/group created and configured correctly?
  2. *Privilege Understanding* (40%) — Does the demonstration correctly show the privilege distinction?

---

### Activity 2.5 — Text Processing & Log Reading
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 2.2
- **Evidence Required:** Screenshot + URL/Link
- **Review & Collaboration:** None
- **Prompt:** Learn `grep`, `find`, `cat`/`less`/`tail`, and piping with `awk`/`sort`/`uniq`. Given a real system log on their EC2 instance (e.g., `/var/log/syslog` or `/var/log/cloud-init.log`), fellow writes commands to find specific event types, count occurrences, and tail live output — the exact skill set needed to diagnose a real server issue.
- **Rubric:**
  1. *Command Correctness* (70%) — Do the commands correctly produce the requested results?
  2. *Efficiency* (30%) — Are pipes/combinations used sensibly?

---

### Activity 2.6 — Your First Server Automation Script
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 2.5 | **Prerequisites:** Activity 2.5, Activity 2.4
- **Evidence Required:** GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Write a Bash script that automates a genuinely useful cloud-relevant task — e.g., a script that checks disk usage and warns if above a threshold, or one that checks for and reports any world-writable files (a real security hygiene check). Include comments explaining each section, and push to a GitHub repo.
- **Rubric:**
  1. *Functionality* (50%) — Does the script correctly do what it claims?
  2. *Code Quality* (30%) — Reasonably clean, commented, handles basic edge cases?
  3. *Cloud Relevance* (20%) — Does the script address a genuinely plausible server-ops problem?

---

### Activity 2.7 — Debug Challenge: The Broken Server
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 2.6 | **Prerequisites:** Activity 2.6
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given a deliberately broken EC2 setup scenario (platform-provided: a web server that won't start due to a permissions issue, a full disk, or a misconfigured service) and must diagnose and fix it using the skills from this Milestone, documenting their diagnostic process step by step (what they checked, what they found, how they fixed it) — mirroring the real "3am the server is down" experience in a safe, guided context.
- **Rubric:**
  1. *Correct Diagnosis* (40%) — Is the root cause correctly identified?
  2. *Correct Fix* (40%) — Is the server genuinely working again afterward?
  3. *Process Documentation* (20%) — Is the diagnostic process clearly and honestly documented?

---

### Activity 2.8 — Brand: Terminal Skills for the Cloud
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.7 | **Prerequisites:** Activity 2.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video walking through the Debug Challenge from Activity 2.7 — the broken server scenario, the diagnostic process, and the fix. This is exactly the kind of "I fixed a real problem" writeup that resonates with hiring managers.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Authenticity* (30%) — Genuine, personal explanation, not generic copy?

---

### Activity 2.9 — Mock Interview: Linux Fundamentals Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.6, Activity 2.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain the rwx permission model, why SSH key permissions matter, principle of least privilege, and a walkthrough of their Debug Challenge diagnostic process.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 2 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 2.1 | Why Linux Runs the Cloud | Reflection | Beginner | 5 | Required |
| 2.2 | Filesystem Navigation | Workshop | Beginner | 10 | Required |
| 2.3 | File Permissions & Ownership | Workshop | Beginner | 15 | Required |
| 2.4 | Users, Groups & Privilege Basics | Workshop | Beginner | 10 | Required |
| 2.5 | Text Processing & Log Reading | Workshop | Intermediate | 15 | Required |
| 2.6 | Your First Server Automation Script | Project | Intermediate | 20 | Required |
| 2.7 | Debug Challenge: The Broken Server | Debug Challenge | Intermediate | 20 | Required |
| 2.8 | Terminal Skills for the Cloud (Brand) | Blog Post | Beginner | 10 | Required |
| 2.9 | Mock Interview: Linux Fundamentals Check | Mock Interview | Intermediate | 10 | Required |

**Milestone 2 total (if all completed):** 115 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–2)
- Milestone 1: 95 pts
- Milestone 2: 115 pts
- **Cumulative possible so far:** 210 pts

---

## Operational Note
Activities 2.2, 2.3, 2.6, and 2.7 all require fellows to launch and terminate real EC2 instances repeatedly — worth reinforcing the Cost Discipline Gate habits at every single one of these touch points (already built into 2.2's rubric as an example) rather than assuming it "sticks" from Milestone 1 alone. Consider whether the platform should send an automated reminder/nudge each time a resource-provisioning activity is opened.

---

## What's Next: Milestone 3 Preview
**Networking Fundamentals for Cloud** — self-contained OSI/TCP-IP, IP addressing, ports/protocols, and DNS, framed specifically around what VPCs will later formalize (Milestone 7). Say the word when ready.
