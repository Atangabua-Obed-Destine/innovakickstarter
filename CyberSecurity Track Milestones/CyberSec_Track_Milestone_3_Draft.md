# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 3: Linux & Command Line Fundamentals

**Status:** Draft for review
**Unlocks:** After Milestone 2 is fully completed
**Theme:** Comfort in the terminal — filesystem navigation, permissions, users/groups, and basic shell scripting. Nearly every security tool from this point forward (Nmap, Metasploit, log analysis, SIEM CLIs) assumes this foundation.
**Target fellow:** Has passed the Ethics Gate; comfortable with basic networking concepts; may have never used a terminal before.

---

### Activity 3.1 — Why Linux Dominates Security
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Ethics Gate)
- **Evidence Required:** Text Response
- **Prompt:** Fellow researches why Linux (particularly distros like Kali) is the industry-standard OS for security work — open source tooling, scripting power, server-world prevalence — and writes a short reflection connecting this to their own Kali setup from Milestone 1.
- **Rubric:**
  1. *Understanding* (70%) — Correct reasoning about why Linux dominates this field?
  2. *Personal Connection* (30%) — Ties back to their own setup experience?

---

### Activity 3.2 — Filesystem Navigation
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.1 | **Prerequisites:** Activity 3.1
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Learn `pwd`, `ls`, `cd`, `mkdir`, `touch`, `cp`, `mv`, `rm`, and the Linux filesystem hierarchy (`/etc`, `/home`, `/var`, `/tmp`, etc.). Fellow creates a small folder structure (e.g., a `security-lab` directory with subfolders for notes, tools, and scripts), navigates it, and screenshots the terminal history showing each command used.
- **Rubric:**
  1. *Command Correctness* (70%) — Are commands used correctly to achieve the structure?
  2. *Understanding of Hierarchy* (30%) — Does the folder structure/notes reflect real understanding of where things belong?

---

### Activity 3.3 — File Permissions & Ownership
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.2 | **Prerequisites:** Activity 3.2
- **Evidence Required:** File Upload (terminal screenshots) + Text Response
- **Prompt:** Learn `chmod`, `chown`, the `rwx` permission model (owner/group/other), and numeric vs. symbolic notation. Fellow creates a script file, deliberately sets it to non-executable, tries to run it (documenting the failure), then correctly fixes permissions and runs it successfully — explaining what each permission bit means in their own words.
- **Rubric:**
  1. *Correct Permission Manipulation* (60%) — Are `chmod`/`chown` used correctly to fix the problem?
  2. *Conceptual Explanation* (40%) — Is the rwx model correctly explained?

---

### Activity 3.4 — Users, Groups & Privilege Basics
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.3
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Learn user/group concepts, `sudo`, and the principle of least privilege (why root shouldn't be used for everyday tasks). Fellow creates a new non-root user, adds them to a group, and demonstrates a `sudo`-required action failing for the new user versus succeeding for an authorized one.
- **Rubric:**
  1. *Correct Setup* (60%) — Is the user/group created and configured correctly?
  2. *Privilege Understanding* (40%) — Does the demonstration correctly show the privilege distinction?

---

### Activity 3.5 — Text Processing & Searching (grep, find, piping)
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 3.2
- **Evidence Required:** File Upload (terminal screenshots) + URL/Link (command history file)
- **Prompt:** Learn `grep`, `find`, `cat`/`less`, and piping (`|`) with `awk`/`sort`/`uniq` at a basic level. Given a sample log file (fellow can generate one or use a provided sample), fellow writes commands to: find all lines containing "failed login," count how many unique IP addresses appear, and find all files modified in the last 24 hours in a directory.
- **Rubric:**
  1. *Command Correctness* (70%) — Do the commands correctly produce the requested results?
  2. *Efficiency* (30%) — Are pipes/combinations used sensibly rather than overly manual approaches?

---

### Activity 3.6 — Your First Bash Script
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.5 | **Prerequisites:** Activity 3.5, Activity 3.4
- **Evidence Required:** URL/Link (GitHub repo)
- **Prompt:** Write a small Bash script that automates something genuinely useful for a security context — e.g., a script that checks a given directory for files with overly permissive permissions (world-writable) and lists them, or one that scans a log file for failed login attempts and reports the top 5 offending IPs. Include comments explaining each section.
- **Rubric:**
  1. *Functionality* (50%) — Does the script correctly do what it claims?
  2. *Code Quality* (30%) — Reasonably clean, commented, handles basic edge cases (e.g., missing arguments)?
  3. *Security Relevance* (20%) — Does the script solve a genuinely plausible security/ops problem?

---

### Activity 3.7 — Hands-On: Linux Fundamentals Lab Room
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 3.4, Activity 3.5
- **Evidence Required:** Text Response (flags) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner Linux-fundamentals-focused TryHackMe or OverTheWire room (e.g., OverTheWire's "Bandit" wargame, levels 0–10, is an excellent fit here — pure command-line problem-solving). Submit flags/passwords for each level completed and a short reflection on the trickiest one.
- **Rubric:**
  1. *Correct Completion* (70%) — Are the required levels correctly completed with proof?
  2. *Reflection Quality* (30%) — Genuine insight into the trickiest challenge?

---

### Activity 3.8 — Brand: Terminal Skills in Action
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.6 | **Prerequisites:** Activity 3.6, Activity 3.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video walking through the Bash script built in Activity 3.6 — what it does, why it's useful, and a live demo of it running. Alternatively (fellow's choice), a Bandit wargame writeup covering 3–4 of the trickiest levels, if the platform's rules permit public writeups for those levels.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Technical Accuracy* (15%) — Correctly explains the technical content?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 3.9 — Mock Interview: Linux Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.6, Activity 3.7
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Explain the rwx permission model, principle of least privilege, walk through their Bash script's logic, and how they approached the Bandit/lab challenges.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 3 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 3.1 | Why Linux Dominates Security | learning | Beginner | 5 | Required |
| 3.2 | Filesystem Navigation | learning | Beginner | 10 | Required |
| 3.3 | File Permissions & Ownership | learning | Beginner | 15 | Required |
| 3.4 | Users, Groups & Privilege Basics | learning | Beginner | 10 | Required |
| 3.5 | Text Processing & Searching | learning | Intermediate | 15 | Required |
| 3.6 | Your First Bash Script | project | Intermediate | 20 | Required |
| 3.7 | Hands-On: Linux Fundamentals Lab Room | project | Intermediate | 15 | Required |
| 3.8 | Terminal Skills in Action (Brand) | blog_post | Beginner | 10 | Required |
| 3.9 | Mock Interview: Linux Fundamentals Check | mock_interview | Intermediate | 10 | Required |

**Milestone 3 total (if all completed):** 110 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–3)
- Milestone 1: 75 pts
- Milestone 2: 105 pts
- Milestone 3: 110 pts
- **Cumulative possible so far:** 290 pts

---

## What's Next: Milestone 4 Preview
**Core Security Concepts** — CIA triad, threat/vulnerability/risk vocabulary, common attack categories (conceptual overview: malware, phishing, social engineering, DoS, MITM), and security policy basics. This builds the shared vocabulary needed before Cryptography (Milestone 5) and the hands-on Applied Foundations arc. Say the word when ready.
