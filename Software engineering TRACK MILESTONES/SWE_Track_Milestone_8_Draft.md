# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 8: Git Branching & Real-World Collaboration

**Status:** Draft for review
**Unlocks:** After Milestone 7 is fully completed
**Theme:** Moving beyond solo, single-branch commits into how real engineering teams work — branches, merge conflicts, pull requests, and code review. Directly relevant to how fellows will eventually contribute to I-NNOVA CM's real client codebases.
**Target fellow:** Comfortable with basic `git add/commit/push`; has never branched, merged, or opened a pull request.

---

### Activity 8.1 — Why Teams Don't Commit Straight to Main
- **Type:** `learning`
- **Pillar:** Collaborate
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.7
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://www.atlassian.com/git/tutorials/using-branches
- **Prompt:** Fellow researches why real teams use branching workflows instead of everyone committing directly to `main`. Write a short explanation covering risk of breaking production, parallel work by multiple developers, and code review as a quality gate.
- **Rubric:**
  1. *Conceptual Accuracy* (70%) — Correct reasoning about why branching matters?
  2. *Real-World Connection* (30%) — Does the explanation connect to a plausible real scenario (e.g., two people editing the same file)?

---

### Activity 8.2 — Branching Basics
- **Type:** `project`
- **Pillar:** Collaborate
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 8.1 | **Prerequisites:** Activity 8.1
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-branches
- **Prompt:** In an existing repo (e.g., the Milestone 7 project), create a new branch, make a small isolated change (e.g., add a new field or route), commit it on that branch, and push the branch to GitHub without merging it into `main` yet.
- **Rubric:**
  1. *Correct Branch Usage* (60%) — Is the change isolated to a properly named branch, not on `main`?
  2. *Commit Hygiene* (40%) — Are commit messages clear and meaningful?

---

### Activity 8.3 — Pull Requests & Code Review
- **Type:** `learning`
- **Pillar:** Collaborate
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 8.2 | **Prerequisites:** Activity 8.2
- **Evidence Required:** URL/Link (GitHub PR link)
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests
- **Prompt:** Open a pull request from the Activity 8.2 branch into `main`, writing a clear PR description (what changed, why). Fellow also reviews a peer's PR (or a sample PR provided by a mentor) leaving at least 2 substantive comments — not just "looks good."
- **Rubric:**
  1. *PR Quality* (50%) — Clear title/description explaining the change?
  2. *Review Quality* (50%) — Are peer review comments specific and useful (pointing at real code, asking real questions)?

---

### Activity 8.4 — Resolving Merge Conflicts
- **Type:** `learning`
- **Pillar:** Collaborate
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.3 | **Prerequisites:** Activity 8.3
- **Evidence Required:** URL/Link (GitHub repo) + Text Response
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/addressing-merge-conflicts/resolving-a-merge-conflict-using-the-command-line
- **Prompt:** Fellow deliberately creates a merge conflict (two branches editing the same line/file differently), then resolves it correctly using Git's conflict markers, and writes a short explanation of what caused the conflict and how they decided which changes to keep.
- **Rubric:**
  1. *Correct Resolution* (60%) — Is the conflict resolved cleanly with no leftover conflict markers or lost work?
  2. *Explanation Quality* (40%) — Is the reasoning about the conflict and resolution choice clear?

---

### Activity 8.5 — Problem Solving 108: Git History Detective
- **Type:** `learning`
- **Pillar:** Collaborate
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 8.4
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://www.atlassian.com/git/tutorials/undoing-changes
- **Prompt:** Learn `git log`, `git blame`, `git diff`, and `git revert`/`git reset` (conceptually — when to use which, and why `reset` on shared branches is dangerous). Given a repo with a deliberately introduced bug in its history, fellow uses these tools to identify which commit introduced the bug and explains how they'd safely fix it (revert vs. new fix commit) without rewriting shared history.
- **Rubric:**
  1. *Correct Investigation* (50%) — Did they correctly identify the problematic commit using the right tools?
  2. *Safe Resolution Reasoning* (50%) — Do they correctly reason about why rewriting shared history is risky, and choose an appropriate safe fix?

---

### Activity 8.6 — Project: Collaborative Feature Contribution
- **Type:** `project`
- **Pillar:** Collaborate
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 8.3, Activity 8.4
- **Evidence Required:** URL/Link (GitHub PR, merged)
- **Resources:** https://www.youtube.com/watch?v=mAFoROnOfHs, https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests
- **Prompt:** Paired with another fellow (or contributing to a shared sample repo), fellow proposes and implements one small feature via a full branch → PR → review → merge workflow, incorporating at least one round of review feedback before merging (i.e., the first PR version must actually get a requested change, and the fellow must address it).
- **Rubric:**
  1. *Workflow Correctness* (40%) — Was the full branch/PR/review/merge cycle followed correctly?
  2. *Responsiveness to Feedback* (30%) — Was review feedback genuinely incorporated (not ignored or superficially addressed)?
  3. *Feature Quality* (30%) — Does the merged feature actually work as intended?

---

### Activity 8.7 — Brand: Lessons from Collaborating on Code
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 8.6 | **Prerequisites:** Activity 8.6
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup about the experience of going through a real PR/review cycle for the first time — what feedback they got, how they responded, and what they learned about working with other developers. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine reflection, not a generic post.
  2. *Insight Quality* (50%) — Does it show real understanding of collaborative workflows, not just "I made a PR"?

---

### Activity 8.8 — Mock Interview: Collaboration & Git Workflow Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 8.5, Activity 8.6
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain the branch/PR/review/merge workflow, how they resolved a merge conflict, why `git reset` is risky on shared branches, and how they responded to review feedback on their collaborative project.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 8 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 8.1 | Why Teams Don't Commit Straight to Main | learning | Beginner | 5 | Required |
| 8.2 | Branching Basics | project | Beginner | 10 | Required |
| 8.3 | Pull Requests & Code Review | learning | Beginner | 10 | Required |
| 8.4 | Resolving Merge Conflicts | learning | Intermediate | 15 | Required |
| 8.5 | Problem Solving 108: Git History Detective | learning | Intermediate | 15 | Required |
| 8.6 | Collaborative Feature Contribution | project | Intermediate | 20 | Required |
| 8.7 | Lessons from Collaborating on Code (Brand) | blog_post | Beginner | 5 | Optional |
| 8.8 | Mock Interview: Collaboration & Git Workflow Check | mock_interview | Intermediate | 10 | Required |

**Milestone 8 total (if all completed):** 90 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–8)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- Milestone 4: 95 pts
- Milestone 5: 90 pts
- Milestone 6: 105 pts
- Milestone 7: 120 pts
- Milestone 8: 90 pts
- **Cumulative possible so far:** 745 pts

---

## Note on Activity 8.6
This one needs real infrastructure — either a "buddy system" pairing fellows at similar progress points, or a shared sandbox repo maintained by admins/mentors that fellows contribute small features to. Worth flagging as an operational dependency (not just curriculum content) before this Milestone goes live: how will pairing/matching actually happen on the platform?

---

## What's Next: Milestone 9 Preview
As planned, **Authentication & Authorization** is next — password hashing, sessions vs. JWTs, protecting routes, and role-based access (directly relevant given your platform's own Rookie/Intern/Professional/Elite role distinctions, and EduTrust Pay's need for secure access). Say the word when ready.
