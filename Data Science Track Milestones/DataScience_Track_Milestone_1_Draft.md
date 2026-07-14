# I-NNOVA KICKSTARTER — Data Science Track
## Milestone 1: Welcome to Data Science

**Status:** Draft for review
**Unlocks:** Immediately upon enrollment
**Theme:** Orientation, career paths, an early seed of data ethics, toolkit setup, and the first meeting with **Nkwen Traders Ltd.** — a fictional Cameroonian retail chain whose deliberately messy, evolving sales data will recur across this entire Foundations Arc.
**Target fellow:** Zero prior programming, statistics, or data experience assumed.

---

### Meet Nkwen Traders Ltd. 🏪
Throughout this track, fellows will repeatedly return to data from **Nkwen Traders Ltd.**, a fictional multi-branch retail chain (inspired by, but never copying, real systems like POSINNOVA/Pacesetters) with stores across several fictional Cameroonian towns. Each Milestone introduces a new "chapter" of Nkwen Traders' data — starting messy and small, growing richer (new columns, new stores, new problems) as fellows' skills grow. This gives the track a consistent narrative thread the way the portfolio site did in Software Engineering, and mirrors how real data work rarely starts from a blank slate.

> **Platform note:** This means a small but reusable content asset is needed — a maintained, versioned set of Nkwen Traders CSV files (Chapter 1 through however far the track goes), ideally hosted as a public Kaggle Dataset so fellows can load it directly into their notebooks. Worth scoping as a one-time content build that pays off across many Milestones.

---

### Activity 1.1 — What Is Data Science, Really?
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** None
- **Evidence Required:** Written Submission
- **Prompt:** Fellow reads/watches a curated overview of what data scientists actually do — it's far more than "building models": cleaning messy data, asking the right questions, communicating findings to non-technical stakeholders, and knowing when a model is trustworthy versus dangerous. Write a short reflection on which part excites them most, plus one example (from news, work, or daily life) of a decision that could have been improved with better data analysis.
- **Rubric:**
  1. *Understanding* (60%) — Does the reflection show real grasp of the field's breadth beyond "machine learning"?
  2. *Real-World Grounding* (40%) — Is the example relevant and thoughtfully reasoned?

---

### Activity 1.2 — Career Paths in Data
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.1 | **Prerequisites:** Activity 1.1
- **Evidence Required:** Written Submission
- **Prompt:** Fellow researches the major paths this track will eventually branch into — Data Analytics/BI, Data Science/ML, Data Engineering, and MLOps — and writes a short comparison: what a typical day looks like in each, and which currently appeals to them most (understanding this isn't a binding choice yet).
- **Rubric:**
  1. *Accuracy* (60%) — Are the four paths correctly and distinctly described?
  2. *Self-Reflection* (40%) — Is their stated interest reasoned, not arbitrary?

---

### Activity 1.3 — Data Ethics: The Responsible Data Scientist
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.1
- **Evidence Required:** Written Submission
- **Prompt:** An early seed of a theme that gets a full dedicated Milestone later (Milestone 15): data can encode bias, models can cause real harm when wrong, and privacy matters even with "just numbers." Fellow reads a short, accessible real-world example of a biased or harmful data/AI system (e.g., a well-documented hiring algorithm or facial recognition case, using only public reporting) and writes a reflection on what went wrong and why "the model was accurate on the training data" isn't the same as "the model was fair or safe."
- **Rubric:**
  1. *Understanding* (60%) — Is the harm/bias correctly understood and explained?
  2. *Critical Thinking* (40%) — Does the reflection show genuine grappling with "accurate ≠ fair," not just repeating the example?

---

### Activity 1.4 — Setting Up Your Data Science Toolkit
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.1
- **Evidence Required:** Screenshot
- **Prompt:** Set up a Google Colab account (zero local install needed, ideal for low-spec machines and unreliable local setups) and a free Kaggle account. Create one blank notebook in each, run a simple `print("Hello, Nkwen Traders!")` cell in both, and screenshot the working output.
- **Rubric:**
  1. *Completeness* (100%) — Are both environments functional with the test cell run successfully?

---

### Activity 1.5 — Meet the Dataset: Nkwen Traders, Chapter 1
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.4 | **Prerequisites:** Activity 1.4
- **Evidence Required:** URL/Link (public Kaggle notebook)
- **Prompt:** Load Nkwen Traders' Chapter 1 dataset (a small, single-store, deliberately imperfect sales CSV — a handful of missing values and an obvious typo or two, nothing overwhelming yet) into a Kaggle notebook using pandas' `read_csv`. Without fixing anything yet, fellow explores it with `.head()`, `.info()`, `.shape`, and `.describe()`, and writes markdown cells noting 3 things that look "off" about the data just from this first look.
- **Rubric:**
  1. *Correct Exploration* (60%) — Are the exploration functions correctly used and interpreted?
  2. *Observation Quality* (40%) — Are the "off" observations genuine and specific (not vague)?

---

### Activity 1.6 — Problem Solving 101: Thinking Like a Data Scientist
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.5
- **Evidence Required:** Written Submission
- **Prompt:** Introduce a simple "question → data → answer" framing that underlies all data work. Given a vague business question about Nkwen Traders (e.g., "why did sales drop last month?"), fellow practices turning it into 3–4 specific, answerable sub-questions (e.g., "did drop happen across all products or specific ones?", "did it coincide with a stockout, a price change, or a holiday?") — the habit of decomposing vague questions into checkable ones, before any code is written.
- **Rubric:**
  1. *Decomposition Quality* (60%) — Are the sub-questions specific, distinct, and genuinely answerable with data?
  2. *Business Relevance* (40%) — Do the questions actually help address the original vague question?

---

### Activity 1.7 — Brand: My First Look at Data
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 1.5 | **Prerequisites:** Activity 1.5
- **Evidence Required:** URL/Link (article — e.g., LinkedIn or Kaggle notebook with narrative markdown) **AND** Video Recording
- **Prompt:** Write a short article and record a short video walking through the Nkwen Traders Chapter 1 exploration — what tools were used, what was found "off" about the data, and why first impressions of raw data matter before jumping to conclusions. Tag/hashtag per platform convention.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, well-structured writeup?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Authenticity* (30%) — Genuine, personal explanation, not generic copy?

---

### Activity 1.8 — Mock Interview: Orientation Check-In
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 1.3, Activity 1.6
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 65/100
- **Required Sessions:** 1
- **Focus:** Behavioral questions ("why data science?"), a light ethics-scenario question echoing Activity 1.3, and a walkthrough of their question-decomposition reasoning from Activity 1.6.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 1 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 1.1 | What Is Data Science, Really? | learning | Beginner | 5 | Required |
| 1.2 | Career Paths in Data | learning | Beginner | 5 | Required |
| 1.3 | Data Ethics: The Responsible Data Scientist | learning | Beginner | 10 | Required |
| 1.4 | Setting Up Your Data Science Toolkit | project | Beginner | 10 | Required |
| 1.5 | Meet the Dataset: Nkwen Traders, Chapter 1 | project | Beginner | 15 | Required |
| 1.6 | Problem Solving 101: Thinking Like a Data Scientist | learning | Beginner | 10 | Required |
| 1.7 | My First Look at Data (Brand) | blog_post | Beginner | 10 | Required |
| 1.8 | Mock Interview: Orientation Check-In | mock_interview | Beginner | 10 | Required |

**Milestone 1 total (if all completed):** 75 points (raw, pre-multiplier)

---

## Evidence Requirement Key (per platform's actual categories)
This Milestone uses: **Written Submission**, **Screenshot**, **URL/Link**, **Video Recording**, **Interview Session** — all confirmed against your platform's real Evidence Requirements list. I'll keep using these exact category names going forward (rather than the more generic labels used in earlier SWE/Cybersecurity drafts) — let me know if you'd like those older documents' evidence fields relabeled to match.

---

## Platform/Operational Notes
1. **Nkwen Traders dataset build** — flagged above: needs a small, versioned set of CSVs released chapter-by-chapter across Milestones, ideally hosted as a public Kaggle Dataset. Worth scoping early since almost every future Milestone depends on it existing.
2. **Colab vs. local setup** — chose Google Colab over local Jupyter/Anaconda installs specifically because it removes hardware/setup friction for fellows on lower-spec machines or unreliable power/internet — worth confirming this matches your infrastructure assumptions for fellows in Bamenda and beyond.

---

## What's Next: Milestone 2 Preview
**Python Fundamentals for Data** — Python from zero (variables, control flow, functions, core data structures like lists/dicts), taught with data-flavored examples from the start (e.g., looping over a list of Nkwen Traders product names) rather than generic exercises, so fellows immediately see why they're learning each concept. Say the word when ready.
