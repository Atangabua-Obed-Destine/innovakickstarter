# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 1: Welcome to Cybersecurity

**Status:** Draft for review
**Unlocks:** Immediately upon enrollment
**Theme:** Orientation, career paths, and — critically — the ethics/legal foundation that gates everything hands-on in this track. No offensive or defensive tooling unlocks until the ethics gate is passed.
**Target fellow:** Zero prior security, networking, or Linux experience assumed.

> **Design note:** This Milestone establishes a structural pattern unique to this track: **Activity 1.3 (Ethics & Legal Gate) is a hard prerequisite for every hands-on activity in the entire track**, not just this Milestone. Recommend the platform enforce this at a system level (e.g., a `requires_ethics_gate: true` flag checked before any lab-evidence activity unlocks, track-wide) rather than only wiring it as a per-Milestone prerequisite — worth a platform/engineering conversation.

---

### Activity 1.1 — What Is Cybersecurity, Really?
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** None
- **Evidence Required:** Text Response
- **Prompt:** Fellow reads/watches a curated overview of what security professionals actually do day-to-day — it's far more than "hacking": monitoring, patching, writing policy, responding to incidents, testing defenses. Write a short reflection on which part excites them most and why, plus one real-world breach they've heard of (or research one) and what likely went wrong at a high level.
- **Rubric:**
  1. *Understanding* (60%) — Does the reflection show real grasp of the field's breadth beyond "hacking"?
  2. *Real-World Grounding* (40%) — Is the breach example relevant and reasonably well understood?

---

### Activity 1.2 — Career Paths in Security
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.1 | **Prerequisites:** Activity 1.1
- **Evidence Required:** Text Response
- **Prompt:** Fellow researches the four major paths this track will eventually branch into — Red Team (offensive/pentesting), Blue Team (defensive/SOC), GRC (governance, risk & compliance), and specializations like cloud security or digital forensics — and writes a short comparison: what a typical day looks like in each, and which one currently appeals to them most (with the understanding this isn't a binding choice yet).
- **Rubric:**
  1. *Accuracy* (60%) — Are the four paths correctly and distinctly described?
  2. *Self-Reflection* (40%) — Is their stated interest reasoned, not arbitrary?

---

### Activity 1.3 — The Ethics & Legal Gate ⚠️ (Hard Prerequisite)
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** N/A (this activity cannot be skipped or late-penalized around — it blocks progress entirely until passed)
- **Chain Parent:** None | **Prerequisites:** Activity 1.1
- **Evidence Required:** Text Response + a signed digital acknowledgment ("White Hat Pledge")
- **Prompt:** Fellow works through material covering: the legal boundary between authorized and unauthorized access (only ever testing systems you own or have explicit written permission to test — including lab environments provided by the platform), relevant computer misuse principles, real consequences of unauthorized access (career-ending and criminal, with real anonymized case examples), and responsible disclosure basics (what to do if you accidentally find a real vulnerability outside a lab). Fellow then answers 5 scenario-based questions ("Is this action authorized or not?") and digitally signs a White Hat Pledge committing to ethical practice as a condition of continuing the track.
- **Rubric:**
  1. *Scenario Accuracy* (60%) — Are all 5 authorized/unauthorized scenarios correctly identified? (Recommend requiring 100% — this is not a partial-credit topic.)
  2. *Understanding* (40%) — Does the written response show genuine comprehension, not just pattern-matching answers?
- **Passing requirement:** 100% on scenario questions to unlock any further hands-on activity in the track. Fellow may retake if they fail — this is a learning gate, not a punitive one.

---

### Activity 1.4 — Setting Up Your Security Toolkit
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (gated)
- **Evidence Required:** File Upload (screenshot)
- **Prompt:** Set up Kali Linux — either as a virtual machine (VirtualBox/VMware, free tier) or via WSL on Windows — and create free accounts on TryHackMe and OverTheWire. Screenshot showing Kali running with a terminal open, and both platform accounts logged in.
- **Rubric:**
  1. *Completeness* (100%) — Is Kali functional and are both lab platform accounts created?

---

### Activity 1.5 — Your First Flag
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 1.4 | **Prerequisites:** Activity 1.4, Activity 1.3 (gated)
- **Evidence Required:** Text Response (flag submitted) + URL/Link (screenshot/proof of completion on the lab platform)
- **Prompt:** Complete TryHackMe's beginner-friendly introductory room (e.g., their official "Intro to Cyber Security" or "Pre Security" starter room), submitting the flag(s) found. This is fellows' first hands-on lab experience — low stakes, guided, confidence-building.
- **Rubric:**
  1. *Correct Flag Submission* (70%) — Is the correct flag submitted with proof of completion?
  2. *Reflection* (30%, folded into Text Response) — Brief note on what the challenge taught them.

---

### Activity 1.6 — Security Mindset 101: Thinking Like an Attacker and a Defender
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (gated)
- **Evidence Required:** Text Response
- **Prompt:** Introduce basic threat-modeling thinking (assets, threats, vulnerabilities, likelihood/impact) using a simple, relatable scenario — e.g., "model the threats to a small shop's point-of-sale system" (deliberately close to real I-NNOVA CM territory, without touching any real system). Fellow lists at least 5 plausible threats, rates likelihood/impact for each, and suggests one mitigation per threat.
- **Rubric:**
  1. *Threat Identification* (40%) — Are the threats realistic and relevant to the scenario?
  2. *Risk Reasoning* (30%) — Is likelihood/impact reasoning sound?
  3. *Mitigation Quality* (30%) — Are proposed mitigations sensible and specific?

---

### Activity 1.7 — Brand: Your First Security Writeup & Video
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10% (Required — not optional, per track-wide Brand policy)
- **Chain Parent:** Activity 1.5 | **Prerequisites:** Activity 1.5
- **Evidence Required:** URL/Link (written article — LinkedIn article, Medium, or personal blog) **AND** URL/Link (short video — unlisted or public YouTube, 3–7 minutes)
- **Prompt:** Fellow produces two linked pieces of content documenting Activity 1.5's TryHackMe room: **(1)** a short written report/writeup — what the room covered, the approach taken, and what was learned (standard "CTF writeup" format, an established genre in the security community) and **(2)** a short video walking through the same content out loud, screen-recorded, explaining their thinking as if teaching someone else. No flags, credentials, or answers to unreleased/paid rooms should be shared irresponsibly if the platform's rules restrict writeups — fellow must check and follow TryHackMe's own writeup policy for that specific room.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, well-structured writeup showing genuine understanding?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality (audible, screen visible, coherent)?
  3. *Platform Policy Compliance* (15%) — Did they check and follow the lab platform's writeup/disclosure rules for that room?
  4. *Authenticity* (15%) — Does it read/sound like genuine understanding, not a script read blindly?

---

### Activity 1.8 — Mock Interview: Orientation & Ethics Check-In
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (gated), Activity 1.6
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Behavioral questions ("why security, not just software development?"), a couple of ethics-scenario questions similar to Activity 1.3 (reinforcing the gate, not just testing it once), and a walkthrough of their Activity 1.6 threat model.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 1 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 1.1 | What Is Cybersecurity, Really? | learning | Beginner | 5 | Required |
| 1.2 | Career Paths in Security | learning | Beginner | 5 | Required |
| 1.3 | The Ethics & Legal Gate ⚠️ | learning | Beginner | 10 | Required — Hard Gate |
| 1.4 | Setting Up Your Security Toolkit | project | Beginner | 10 | Required |
| 1.5 | Your First Flag | project | Beginner | 15 | Required |
| 1.6 | Security Mindset 101 | learning | Beginner | 10 | Required |
| 1.7 | Your First Security Writeup & Video | blog_post | Beginner | 10 | Required |
| 1.8 | Mock Interview: Orientation & Ethics Check-In | mock_interview | Beginner | 10 | Required |

**Milestone 1 total (if all completed):** 75 points (raw, pre-multiplier)

---

## Platform/Operational Notes
1. **Ethics gate enforcement** — flagging again since it matters: Activity 1.3 should ideally block access to *every* future hands-on activity across the whole track (not just Milestone 1), including in Milestones 7+ we haven't drafted yet. Worth a system-level design decision rather than manually wiring prerequisites into every future Activity.
2. **Lab platform account creation** — Activity 1.4 assumes TryHackMe and OverTheWire both have sufficient free tiers for early content. Worth periodically reconfirming as platforms change their free/paid boundaries.
3. **Writeup policy compliance** — some TryHackMe rooms restrict public writeups until a certain date or entirely. Activity 1.7 explicitly asks fellows to check this, but it's worth curating a known list of "writeup-friendly" rooms for early Milestones to avoid confusion.

---

## What's Next: Milestone 2 Preview
**Networking Fundamentals** — OSI/TCP-IP models, IP addressing, common ports/protocols, and basic packet analysis with Wireshark, all in preparation for the recon/scanning work later in the Applied Foundations arc. Say the word when ready.
