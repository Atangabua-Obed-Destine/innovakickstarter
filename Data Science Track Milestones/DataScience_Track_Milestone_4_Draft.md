# I-NNOVA KICKSTARTER — Data Science Track
## Milestone 4: NumPy & Pandas Fundamentals

**Status:** Draft for review
**Unlocks:** After Milestone 3 is fully completed
**Theme:** The core data manipulation stack everything from here forward depends on. Fellows graduate from hand-written loops over lists/dicts to real arrays and DataFrames — and Nkwen Traders' data grows into **Chapter 2**: multi-column, multi-store, genuinely tabular.
**Target fellow:** Python-fluent, applied-statistics-literate; has never used NumPy or pandas.

---

### Meet Nkwen Traders, Chapter 2 📈
The dataset grows: instead of one branch and a handful of rows, fellows now work with a CSV covering **4 branches, 6 months, multiple product categories** — genuinely too large and multi-dimensional to comfortably hand-loop through, which is exactly the point. This is where pandas stops being "a nice-to-have" and becomes obviously necessary.

---

### Activity 4.1 — NumPy Arrays: Why Not Just Lists?
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 3.7
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn NumPy arrays and vectorized operations, and *why* they're faster and more convenient than looping over Python lists (element-wise math without explicit loops). Fellow converts a list of 20 Nkwen Traders daily sales figures into a NumPy array, performs vectorized operations (add a 10% adjustment to all values, filter values above the mean using boolean indexing), and compares this code to the loop-based equivalent from Milestone 2/3, noting the difference in line count and clarity.
- **Rubric:**
  1. *Correct Usage* (60%) — Are vectorized operations correctly performed?
  2. *Comparative Reflection* (40%) — Is the loop-vs-vectorized comparison genuinely insightful?

---

### Activity 4.2 — Introducing the DataFrame
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.1 | **Prerequisites:** Activity 4.1
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Load Nkwen Traders' Chapter 2 CSV into a pandas DataFrame. Learn `.head()`, `.tail()`, `.shape`, `.columns`, `.dtypes`, `.info()`, `.describe()`. Fellow explores the new, richer dataset and writes markdown notes on what's different/bigger about it compared to Chapter 1, and what new questions this richer data now makes possible to ask.
- **Rubric:**
  1. *Correct Exploration* (60%) — Are exploration methods correctly used?
  2. *Observation Quality* (40%) — Are the noted differences and new possible questions genuine and specific?

---

### Activity 4.3 — Selecting & Filtering Data
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.2 | **Prerequisites:** Activity 4.2
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn column selection, boolean filtering, and `.loc`/`.iloc`. Fellow answers 5 specific questions about the data purely through filtering/selection (e.g., "show all transactions from the Nkwen branch over 50,000 XAF," "show only the product and price columns for the Mile 4 branch in March").
- **Rubric:**
  1. *Correctness* (80%) — Do all 5 queries return correct, complete results?
  2. *Technique* (20%) — Is `.loc`/`.iloc`/boolean filtering used appropriately (not overly convoluted)?

---

### Activity 4.4 — GroupBy: Aggregating Data
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.3 | **Prerequisites:** Activity 4.3
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn `.groupby()` with aggregation functions (`.sum()`, `.mean()`, `.count()`, multiple aggregations via `.agg()`). Fellow answers business questions that require grouping: total revenue by branch, average transaction value by product category, and best-performing branch by month — this is where the "why pandas" argument really lands, since these would be painful to hand-code.
- **Rubric:**
  1. *Correctness* (60%) — Are all groupby aggregations correct?
  2. *Appropriate Technique* (40%) — Is groupby used correctly rather than manually filtering + looping (defeating the purpose)?

---

### Activity 4.5 — Merging & Combining DataFrames
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 4.4
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn `pd.merge()` (inner/left/outer joins) and `pd.concat()`. Fellow is given a second small reference table (branch metadata: branch name, region, manager) and merges it with the main sales data to answer a question requiring both tables (e.g., "total revenue by region," which isn't directly in the sales data alone).
- **Rubric:**
  1. *Correct Merge* (60%) — Is the correct join type used and executed correctly?
  2. *Correct Answer* (40%) — Is the resulting cross-table question correctly answered?

---

### Activity 4.6 — Problem Solving 104: The DataFrame Detective
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.5 | **Prerequisites:** Activity 4.5
- **Evidence Required:** URL/Link (Kaggle/Colab notebook) + Written Submission
- **Prompt:** Fellow is handed a business question with no guidance on which pandas operations to use (e.g., "which branch had the most inconsistent daily sales in Q2, and is there a product category driving that inconsistency?") and must independently plan and execute the multi-step analysis (likely combining groupby, filtering, and the standard-deviation concept from Milestone 3), writing a short explanation of their approach before diving into code.
- **Rubric:**
  1. *Approach Planning* (30%) — Is the written plan sound before coding begins?
  2. *Correct Execution* (50%) — Does the analysis correctly answer the question?
  3. *Technique Appropriateness* (20%) — Are the right pandas tools chosen for the job?

---

### Activity 4.7 — Project: Nkwen Traders Regional Performance Analysis
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 4.6 | **Prerequisites:** Activity 4.6
- **Evidence Required:** URL/Link (public Kaggle notebook)
- **Prompt:** Using the full Chapter 2 dataset (plus the branch metadata table), fellow produces a complete regional performance analysis notebook: revenue by branch and region, top/bottom performing products per branch, month-over-month trend for each branch, and a written executive summary (in markdown) identifying the single most actionable insight for Nkwen Traders' management.
- **Rubric:**
  1. *Analytical Completeness* (35%) — Are all required analyses present and correct?
  2. *Technical Execution* (30%) — Is pandas used correctly and efficiently throughout?
  3. *Executive Summary Quality* (25%) — Is the final insight genuinely actionable and well-communicated?
  4. *Notebook Presentation* (10%) — Clean, organized, readable as a standalone document?

---

### Activity 4.8 — Brand: From Loops to DataFrames
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 4.7 | **Prerequisites:** Activity 4.7
- **Evidence Required:** URL/Link (article) **AND** Video Recording
- **Prompt:** Write an article and record a video walking through the Regional Performance Analysis — the business question, the key insight found, and a "before/after" moment showing how much easier this was with pandas than the pure-Python approach from Milestone 2. A strong, genuinely portfolio-worthy piece.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality?
  3. *Insight Communication* (25%) — Is the key business insight clearly and compellingly presented?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 4.9 — Mock Interview: Pandas Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.6, Activity 4.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain `.loc` vs `.iloc`, how groupby works conceptually, the difference between join types, and a walkthrough of their approach to the Regional Performance Analysis.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 4 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 4.1 | NumPy Arrays: Why Not Just Lists? | learning | Beginner | 10 | Required |
| 4.2 | Introducing the DataFrame | learning | Beginner | 15 | Required |
| 4.3 | Selecting & Filtering Data | learning | Beginner | 15 | Required |
| 4.4 | GroupBy: Aggregating Data | learning | Intermediate | 20 | Required |
| 4.5 | Merging & Combining DataFrames | learning | Intermediate | 15 | Required |
| 4.6 | Problem Solving 104: The DataFrame Detective | learning | Intermediate | 20 | Required |
| 4.7 | Project: Regional Performance Analysis | project | Advanced | 25 | Required |
| 4.8 | From Loops to DataFrames (Brand) | blog_post | Intermediate | 15 | Required |
| 4.9 | Mock Interview: Pandas Fundamentals Check | mock_interview | Intermediate | 10 | Required |

**Milestone 4 total (if all completed):** 145 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–4)
- Milestone 1: 75 pts
- Milestone 2: 120 pts
- Milestone 3: 130 pts
- Milestone 4: 145 pts
- **Cumulative possible so far:** 470 pts

---

## What's Next: Milestone 5 Preview
**Data Wrangling & Cleaning** — Nkwen Traders' data gets genuinely messy (Chapter 3: missing values, duplicates, inconsistent formatting, outliers, mixed types) and fellows learn the unglamorous but essential skill real data scientists spend most of their time on. Say the word when ready.
