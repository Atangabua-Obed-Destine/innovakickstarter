# I-NNOVA KICKSTARTER — Cybersecurity Track
## Milestone 2: Networking Fundamentals

**Status:** Draft for review
**Unlocks:** After Milestone 1 is fully completed (Ethics Gate passed)
**Theme:** How data actually moves across networks — OSI/TCP-IP models, IP addressing, ports, common protocols, and first hands-on packet analysis. This is the bedrock everything else in security (recon, scanning, defense, forensics) sits on top of.
**Target fellow:** Has passed the Ethics Gate; zero prior networking background assumed.

---

### Activity 2.1 — The OSI & TCP/IP Models
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3 (Ethics Gate)
- **Evidence Required:** Text Response
- **Prompt:** Learn the 7-layer OSI model and the simpler 4-layer TCP/IP model, and how they map to each other. Fellow explains, in their own words, what happens at each relevant layer when they load a website — using a real example (e.g., "when I visit google.com, at the network layer...").
- **Rubric:**
  1. *Model Accuracy* (60%) — Are layers and their purposes correctly described?
  2. *Applied Explanation* (40%) — Does the "loading a website" walkthrough correctly map to the model?

---

### Activity 2.2 — IP Addressing & Subnetting Basics
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.1 | **Prerequisites:** Activity 2.1
- **Evidence Required:** Text Response
- **Prompt:** Learn IPv4 addressing, public vs. private IP ranges, and basic subnetting (CIDR notation, subnet masks — just enough to reason about network sizes, not deep subnetting math). Fellow solves 5–6 practice problems (e.g., "how many usable hosts does a /27 support?", "is 192.168.1.5 a private or public address?").
- **Rubric:**
  1. *Correctness* (80%) — Are practice problems answered correctly?
  2. *Reasoning Shown* (20%) — Is the reasoning for at least the subnetting problems shown, not just final answers?

---

### Activity 2.3 — Ports, Protocols & Services
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.2 | **Prerequisites:** Activity 2.2
- **Evidence Required:** Text Response
- **Prompt:** Learn the concept of ports and common well-known ports/protocols (HTTP/80, HTTPS/443, SSH/22, FTP/21, DNS/53, SMTP/25). Fellow builds a reference table matching 10 common ports to their protocol and a one-line description of what each is used for — and flags which of these are commonly considered risky to leave open/unauthenticated (e.g., unencrypted FTP) and why.
- **Rubric:**
  1. *Table Accuracy* (60%) — Are ports/protocols correctly matched?
  2. *Risk Awareness* (40%) — Are risky/legacy protocols correctly flagged with sound reasoning?

---

### Activity 2.4 — DNS: How Names Become Addresses
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.3
- **Evidence Required:** URL/Link (GitHub gist or text file) or File Upload (screenshot)
- **Prompt:** Learn how DNS resolution works (recursive resolver → root → TLD → authoritative server, at a conceptual level) and common record types (A, AAAA, CNAME, MX, TXT). Fellow uses `dig` or `nslookup` in their Kali VM to look up A, MX, and TXT records for 2–3 real domains, and submits the terminal output with a short annotation of what each result means.
- **Rubric:**
  1. *Correct Tool Usage* (50%) — Are the commands run correctly with valid output?
  2. *Interpretation* (50%) — Is the output correctly explained (what each record type means)?

---

### Activity 2.5 — Hands-On: Networking Lab Room
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 2.3, Activity 2.4
- **Evidence Required:** Text Response (flags) + URL/Link (proof of completion)
- **Prompt:** Complete a beginner networking-focused TryHackMe room (e.g., their official "Networking Concepts" or equivalent path room) covering the concepts from 2.1–2.4 in a guided, hands-on format. Submit flags and a short reflection on what clicked that hadn't fully made sense from reading alone.
- **Rubric:**
  1. *Correct Completion* (70%) — Are flags correctly submitted with proof?
  2. *Reflection Quality* (30%) — Does the reflection show genuine "aha moment" understanding?

---

### Activity 2.6 — Problem Solving: Packet Analysis with Wireshark
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 2.5 | **Prerequisites:** Activity 2.5
- **Evidence Required:** File Upload (screenshots) + Text Response
- **Prompt:** Install Wireshark and capture live traffic on their own machine/lab VM while browsing a couple of websites. Fellow identifies and screenshots: a DNS query, a TCP handshake (SYN/SYN-ACK/ACK), and an HTTP or HTTPS request, then writes a short explanation of what's happening in each captured packet.
- **Rubric:**
  1. *Correct Capture & Identification* (60%) — Are the three packet types correctly captured and identified?
  2. *Explanation Accuracy* (40%) — Is the written explanation of each correct?

---

### Activity 2.7 — Brand: Networking Concepts Explained
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 2.6 | **Prerequisites:** Activity 2.6
- **Evidence Required:** URL/Link (article) **AND** URL/Link (video)
- **Prompt:** Write a short article and record a short video explaining one networking concept from this Milestone in plain language (e.g., "How DNS Works" or "What Happens When You Load a Website") — aimed at someone with zero technical background, since teaching a concept simply is one of the best ways to prove real understanding.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Accessibility* (15%) — Is it genuinely understandable to a non-technical audience?
  4. *Authenticity* (15%) — Own words/voice, not copied explanations?

---

### Activity 2.8 — Mock Interview: Networking Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.6, Activity 2.7
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Explain the OSI/TCP-IP models, common ports and their risks, how DNS resolution works, and a walkthrough of what they saw in their Wireshark capture.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 2 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 2.1 | The OSI & TCP/IP Models | learning | Beginner | 10 | Required |
| 2.2 | IP Addressing & Subnetting Basics | learning | Beginner | 15 | Required |
| 2.3 | Ports, Protocols & Services | learning | Beginner | 15 | Required |
| 2.4 | DNS: How Names Become Addresses | learning | Beginner | 10 | Required |
| 2.5 | Hands-On: Networking Lab Room | project | Intermediate | 15 | Required |
| 2.6 | Packet Analysis with Wireshark | learning | Intermediate | 20 | Required |
| 2.7 | Networking Concepts Explained (Brand) | blog_post | Beginner | 10 | Required |
| 2.8 | Mock Interview: Networking Fundamentals Check | mock_interview | Beginner | 10 | Required |

**Milestone 2 total (if all completed):** 105 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–2)
- Milestone 1: 75 pts
- Milestone 2: 105 pts
- **Cumulative possible so far:** 180 pts

---

## What's Next: Milestone 3 Preview
**Linux & Command Line Fundamentals** — filesystem navigation, permissions, users/groups, and basic shell scripting. Nearly every tool used from here forward (Nmap, Metasploit, log analysis, SIEM CLI tools) assumes comfort in a terminal, so this closes that gap before moving into Core Security Concepts and Cryptography. Say the word when ready.
