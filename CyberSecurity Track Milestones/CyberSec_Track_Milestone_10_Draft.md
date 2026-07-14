# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 10: Blue Team Foundations

**Status:** Draft for review
**Unlocks:** After Milestone 9 is fully completed
**Theme:** Deliberately balancing the offensive-heavy focus of Milestones 7–9 with foundational defensive exposure — log analysis, SIEM concepts, and basic incident response workflow. Every fellow gets this regardless of which specialization they eventually choose.
**Target fellow:** Ethics-gated; has completed Foundations Arc, Recon, Scanning, and Web App Security basics.

---

### Activity 10.1 — The Defender's Perspective
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 9.1
- **Evidence Required:** Text Response
- **Prompt:** Having spent Milestones 7–9 largely thinking offensively, fellow reflects on the shift: what does a defender's day actually look like (monitoring, triage, patching, tuning detections) versus an attacker's? Fellow explains the "defender's dilemma" — a defender must protect against every possible attack, while an attacker only needs one success — and what this implies about defensive strategy (defense in depth, detection over pure prevention).
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the defender's dilemma correctly explained?
  2. *Strategic Reasoning* (40%) — Is the defense-in-depth implication correctly reasoned through?

---

### Activity 10.2 — Logs: What Gets Recorded and Why
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 10.1 | **Prerequisites:** Activity 10.1
- **Evidence Required:** File Upload (terminal screenshots)
- **Prompt:** Learn common log sources (system/auth logs like `/var/log/auth.log`, web server access/error logs, application logs) and what each typically records. Fellow inspects real logs on their own Kali VM (e.g., `auth.log` after a few login attempts, including a deliberately failed one) and identifies what a failed vs. successful login entry looks like.
- **Rubric:**
  1. *Correct Inspection* (50%) — Are real log entries correctly located and read?
  2. *Interpretation* (50%) — Are success/failure entries correctly distinguished and explained?

---

### Activity 10.3 — Log Analysis: Finding the Needle
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.2 | **Prerequisites:** Activity 10.2, Activity 3.5
- **Evidence Required:** File Upload (terminal screenshots) + Text Response
- **Prompt:** Given a provided sample log file (simulating a small brute-force attack mixed with normal traffic — platform-generated, not from a real system), fellow uses `grep`/`awk`/`sort`/`uniq` (from Milestone 3) to identify the attacking IP, the targeted username(s), how many attempts were made, and roughly when the attack occurred, writing up findings as a short incident note.
- **Rubric:**
  1. *Correct Identification* (60%) — Is the attack correctly identified from the log data (right IP, timing, pattern)?
  2. *Command Usage* (20%) — Are appropriate CLI tools used effectively?
  3. *Write-up Clarity* (20%) — Is the incident note clear and complete?

---

### Activity 10.4 — Intro to SIEM Concepts
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 10.2
- **Evidence Required:** Text Response
- **Prompt:** Learn what a SIEM (Security Information and Event Management) system does conceptually — centralizing logs from many sources, correlation rules, alerting — and why manual log-checking (like Activity 10.3) doesn't scale for a real organization. Fellow explains the core SIEM workflow (ingest → normalize → correlate → alert → investigate) and why false positives are a constant, real operational challenge for SOC teams.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Is the SIEM workflow correctly explained?
  2. *False Positive Awareness* (40%) — Is the operational challenge correctly understood?

---

### Activity 10.5 — Hands-On: SIEM Lab (Detection & Alerting)
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.4 | **Prerequisites:** Activity 10.4
- **Evidence Required:** Text Response (flags/findings) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner SIEM-focused TryHackMe room (e.g., their "Security Operations" or a Splunk/ELK-based introductory room — TryHackMe has several free-tier SIEM labs using tools like Splunk or the ELK stack). Fellow investigates a simulated incident within the lab's provided SIEM interface and submits findings/flags plus a short reflection on how much faster this felt than manual log-grepping in 10.3.
- **Rubric:**
  1. *Correct Completion* (70%) — Are flags/findings correctly submitted with proof?
  2. *Reflection Quality* (30%) — Genuine comparison drawn to manual analysis?

---

### Activity 10.6 — Incident Response Lifecycle
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 10.4
- **Evidence Required:** Text Response
- **Prompt:** Learn the standard incident response lifecycle (Preparation → Identification → Containment → Eradication → Recovery → Lessons Learned — the NIST/SANS model). Fellow maps their own Activity 10.3 log investigation onto this lifecycle, explaining which phase each of their actions corresponded to, and what the missing phases (containment, eradication, recovery, lessons learned) would look like if this had been a real incident.
- **Rubric:**
  1. *Model Accuracy* (50%) — Is the IR lifecycle correctly described?
  2. *Applied Mapping* (50%) — Is their own prior work correctly mapped, with sound reasoning for the missing phases?

---

### Activity 10.7 — Problem Solving: Full Incident Response Simulation
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 10.6 | **Prerequisites:** Activity 10.6, Activity 10.5
- **Evidence Required:** Text Response
- **Prompt:** Given a realistic, evolving incident scenario delivered in stages (e.g., stage 1: "unusual outbound traffic detected from a workstation"; stage 2 revealed after their initial response: "the workstation was found to have a suspicious scheduled task"; stage 3: "lateral movement attempt detected to a file server"), fellow responds at each stage following the IR lifecycle — containment decisions, what to investigate next, when/how to escalate, and a final incident report summarizing root cause and lessons learned.
- **Rubric:**
  1. *Response Quality Per Stage* (40%) — Are containment/investigation decisions sound at each stage?
  2. *Lifecycle Discipline* (30%) — Is the IR lifecycle correctly followed throughout?
  3. *Final Report Quality* (30%) — Is the root cause analysis and lessons-learned summary clear and complete?

---

### Activity 10.8 — Brand: A Day in the Life of a Defender
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 10.7 | **Prerequisites:** Activity 10.7
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write an article and record a video walking through the incident response simulation from Activity 10.7 — the scenario, key decisions made at each stage, and what the "lessons learned" phase revealed. Frame it as an educational piece for others learning blue team fundamentals.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Educational Value* (15%) — Would this genuinely help another learner understand IR?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 10.9 — Mock Interview: Blue Team Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 10.6, Activity 10.7
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100
- **Required Sessions:** 1
- **Focus:** Explain the IR lifecycle, walk through their decisions in the Activity 10.7 simulation, discuss SIEM concepts and the false-positive challenge, and reflect on offense vs. defense mindset differences.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 10 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 10.1 | The Defender's Perspective | learning | Beginner | 10 | Required |
| 10.2 | Logs: What Gets Recorded and Why | learning | Beginner | 15 | Required |
| 10.3 | Log Analysis: Finding the Needle | project | Intermediate | 20 | Required |
| 10.4 | Intro to SIEM Concepts | learning | Intermediate | 15 | Required |
| 10.5 | Hands-On: SIEM Lab (Detection & Alerting) | project | Intermediate | 20 | Required |
| 10.6 | Incident Response Lifecycle | learning | Intermediate | 15 | Required |
| 10.7 | Problem Solving: Full Incident Response Simulation | learning | Advanced | 25 | Required |
| 10.8 | A Day in the Life of a Defender (Brand) | blog_post | Intermediate | 15 | Required |
| 10.9 | Mock Interview: Blue Team Fundamentals Check | mock_interview | Advanced | 15 | Required |

**Milestone 10 total (if all completed):** 150 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–10)
- Milestones 1–9: 1,050 pts
- Milestone 10: 150 pts
- **Cumulative possible so far:** 1,200 pts

---

## What's Next: Milestone 11 — The Foundations Capstone
With Blue Team Foundations complete, every fellow has now touched OSINT, scanning, web app vulnerabilities, and defensive/incident response work. Milestone 11 is the **consolidation checkpoint** — mirroring the Software Engineering track's capstone structure — likely a single larger scenario requiring fellows to recon, assess, and report on a designated target, or investigate and respond to a simulated incident end-to-end, drawing on everything from Milestones 1–10, before the track forks into Red Team / Blue Team / GRC specialization paths. Say the word when ready to build it.
