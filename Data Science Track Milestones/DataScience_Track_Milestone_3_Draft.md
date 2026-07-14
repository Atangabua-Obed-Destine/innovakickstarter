# I-NNOVA KICKSTARTER — Data Science Track
## Milestone 3: Applied Statistics & Probability I

**Status:** Draft for review
**Unlocks:** After Milestone 2 is fully completed
**Theme:** The first genuinely "data science" (not just programming) Milestone — descriptive statistics, distributions, and basic probability, taught applied-first with Nkwen Traders data throughout rather than abstract math exercises.
**Target fellow:** Python-fluent (variables, control flow, functions, lists/dicts); zero prior statistics background assumed.

---

### Activity 3.1 — Measures of Central Tendency
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 2.7
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn mean, median, and mode, and — critically — *when each is misleading* (e.g., mean skewed by outliers). Using a small Nkwen Traders salary/price list that includes one extreme outlier, fellow calculates all three by hand in Python (no library shortcuts yet) and explains which measure best represents "typical" here and why.
- **Rubric:**
  1. *Correct Calculation* (50%) — Are mean/median/mode correctly computed?
  2. *Outlier Reasoning* (50%) — Is the "which measure is misleading here" reasoning correct and well explained?

---

### Activity 3.2 — Measures of Spread
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.1 | **Prerequisites:** Activity 3.1
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn range, variance, and standard deviation — what they measure (consistency/spread, not just "average"), and why two datasets with the same mean can behave very differently. Fellow compares daily sales at two fictional Nkwen Traders branches with identical average daily sales but very different standard deviations, and writes a business-relevant interpretation (e.g., "Branch A is predictable, Branch B is volatile — which matters for staffing/inventory planning").
- **Rubric:**
  1. *Correct Calculation* (50%) — Are variance/std dev correctly computed?
  2. *Business Interpretation* (50%) — Is the practical meaning of "same average, different spread" correctly explained?

---

### Activity 3.3 — Understanding Distributions
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.2 | **Prerequisites:** Activity 3.2
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn what a distribution is, and get an intuitive (not formula-heavy) introduction to the normal distribution and the idea of skew. Fellow plots a histogram of Nkwen Traders' daily sales data (using matplotlib, taught just enough here to make the plot — full visualization mastery comes in Milestone 6) and describes its shape in plain language (roughly normal? skewed? bimodal — two humps, maybe two different customer patterns?).
- **Rubric:**
  1. *Correct Plotting* (40%) — Is the histogram correctly generated?
  2. *Shape Interpretation* (60%) — Is the distribution shape correctly read and plausibly explained?

---

### Activity 3.4 — Intro to Probability
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.3
- **Evidence Required:** Written Submission
- **Prompt:** Learn basic probability (0–1 scale, independent vs. dependent events, simple conditional probability intuition — not formal Bayes' theorem yet). Fellow solves 5–6 practice problems framed around Nkwen Traders scenarios (e.g., "if 3 out of 10 customers who buy rice also buy sugar, what's the probability a random rice buyer also buys sugar?").
- **Rubric:**
  1. *Correctness* (80%) — Are practice problems answered correctly?
  2. *Reasoning Shown* (20%) — Is the reasoning process shown, not just final answers?

---

### Activity 3.5 — The Empirical Rule & Z-Scores
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.3 | **Prerequisites:** Activity 3.3
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn the empirical rule (68-95-99.7% for normal distributions) and z-scores as a way to standardize and compare values across different scales. Fellow calculates z-scores for a few Nkwen Traders daily sales figures and identifies which day(s) would be considered a genuine statistical outlier (e.g., beyond 2 standard deviations) versus just normal variation.
- **Rubric:**
  1. *Correct Calculation* (60%) — Are z-scores correctly calculated?
  2. *Outlier Identification* (40%) — Is the outlier threshold correctly applied and interpreted?

---

### Activity 3.6 — Problem Solving 103: Detective Work with Descriptive Stats
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.5 | **Prerequisites:** Activity 3.4, Activity 3.5
- **Evidence Required:** Written Submission
- **Prompt:** Fellow is given a short written scenario with only summary statistics (no raw data) for two Nkwen Traders regions' sales performance — mean, median, std dev, and a brief description of shape — and must reason through: which region is more consistent, which might have a data quality issue worth investigating (e.g., median far from mean suggests skew or an error), and what question they'd ask the regional manager next. This builds "statistical detective" instincts before touching inferential statistics in Milestone 8.
- **Rubric:**
  1. *Diagnostic Reasoning* (50%) — Is the interpretation of the summary stats correct and insightful?
  2. *Follow-Up Questions* (30%) — Are the proposed next questions genuinely useful?
  3. *Communication* (20%) — Is this clearly written, as if reporting to a non-technical manager?

---

### Activity 3.7 — Project: Nkwen Traders Statistical Summary Report
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 3.5 | **Prerequisites:** Activity 3.5, Activity 3.6
- **Evidence Required:** URL/Link (public Kaggle notebook)
- **Prompt:** Using Nkwen Traders' Chapter 1 dataset (from Milestone 1), fellow produces a full statistical summary notebook: central tendency and spread for sales/price columns, a histogram with shape interpretation, and identification of any statistical outliers via z-scores — all with markdown commentary explaining findings in plain language, not just raw code output.
- **Rubric:**
  1. *Statistical Correctness* (40%) — Are all calculations correct?
  2. *Interpretation Quality* (40%) — Is the plain-language commentary insightful and accurate?
  3. *Notebook Presentation* (20%) — Is it organized and readable as a standalone document?

---

### Activity 3.8 — Brand: Statistics That Actually Matter
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 3.7 | **Prerequisites:** Activity 3.7
- **Evidence Required:** URL/Link (article) **AND** Video Recording
- **Prompt:** Write an article and record a video explaining why "average" can be misleading (using the mean-vs-median outlier example from Activity 3.1) for a general, non-technical audience — a genuinely useful, widely-relatable data literacy topic.
- **Rubric:**
  1. *Report Quality* (35%) — Clear, accurate, well-structured?
  2. *Video Quality* (35%) — Clear verbal explanation, reasonable production quality?
  3. *Accessibility* (15%) — Genuinely understandable to a non-technical audience?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 3.9 — Mock Interview: Statistics Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.6, Activity 3.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain when mean vs. median is misleading, what a z-score tells you, walk through their statistical detective reasoning from Activity 3.6, and interpret a distribution shape.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 3 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 3.1 | Measures of Central Tendency | learning | Beginner | 10 | Required |
| 3.2 | Measures of Spread | learning | Beginner | 15 | Required |
| 3.3 | Understanding Distributions | learning | Beginner | 15 | Required |
| 3.4 | Intro to Probability | learning | Beginner | 15 | Required |
| 3.5 | The Empirical Rule & Z-Scores | learning | Intermediate | 15 | Required |
| 3.6 | Problem Solving 103: Detective Work with Descriptive Stats | learning | Intermediate | 20 | Required |
| 3.7 | Project: Nkwen Traders Statistical Summary Report | project | Intermediate | 20 | Required |
| 3.8 | Statistics That Actually Matter (Brand) | blog_post | Beginner | 10 | Required |
| 3.9 | Mock Interview: Statistics Fundamentals Check | mock_interview | Intermediate | 10 | Required |

**Milestone 3 total (if all completed):** 130 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–3)
- Milestone 1: 75 pts
- Milestone 2: 120 pts
- Milestone 3: 130 pts
- **Cumulative possible so far:** 325 pts

---

## What's Next: Milestone 4 Preview
**NumPy & Pandas Fundamentals** — the core data manipulation stack that everything from here forward depends on. Fellows will finally stop hand-writing loops over lists of dictionaries and start working with real DataFrames — and Nkwen Traders' data grows into a proper multi-column, multi-store dataset (Chapter 2) to make this feel genuinely necessary rather than arbitrary. Say the word when ready.
