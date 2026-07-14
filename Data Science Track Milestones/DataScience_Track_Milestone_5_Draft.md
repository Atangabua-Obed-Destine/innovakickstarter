# I-NNOVA KICKSTARTER — Data Science Track
## Milestone 5: Data Wrangling & Cleaning

**Status:** Draft for review
**Unlocks:** After Milestone 4 is fully completed
**Theme:** The unglamorous, essential work real data scientists spend most of their time on. Nkwen Traders' data gets genuinely messy — **Chapter 3**: missing values, duplicates, inconsistent formatting, outliers, and mixed types — and fellows learn to clean it properly, not just delete problems away.
**Target fellow:** Comfortable with pandas fundamentals (selection, filtering, groupby, merging).

---

### Meet Nkwen Traders, Chapter 3 🧹
This chapter is deliberately, realistically messy: some prices are stored as text with "XAF" appended, some dates are in three different formats, a handful of duplicate transactions exist from a (fictional) POS syncing glitch, some branch names are inconsistently capitalized ("Nkwen" vs. "nkwen" vs. "NKWEN"), and several rows have missing quantity or price values. This mirrors real messy data fellows will encounter on the job — deliberately, not accidentally.

---

### Activity 5.1 — Why Data Is Never Clean
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 4.7
- **Evidence Required:** Written Submission
- **Prompt:** Fellow loads Nkwen Traders Chapter 3 and, without fixing anything, catalogs every data quality issue they can spot (missing values, inconsistent formatting, likely duplicates, suspicious outliers) using `.info()`, `.isnull().sum()`, and manual inspection. Write a short "data quality audit" listing each issue found and a guess at its real-world cause (e.g., "inconsistent branch capitalization likely came from manual data entry by different staff").
- **Rubric:**
  1. *Thoroughness* (60%) — Are most/all real issues in the dataset correctly identified?
  2. *Root Cause Reasoning* (40%) — Are the guessed causes plausible and thoughtfully reasoned?

---

### Activity 5.2 — Handling Missing Data
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.1 | **Prerequisites:** Activity 5.1
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn the *options* for missing data — drop rows (`.dropna()`), drop columns, fill with a constant, fill with mean/median (`.fillna()`), forward/backward fill for time-series — and critically, that the "right" choice depends on *why* the data is missing and how much is missing. Fellow handles each missing-value case in Chapter 3 differently based on context (e.g., don't drop a whole row just because one non-critical field is missing; do investigate missing prices before assuming a fill is safe), justifying each decision in markdown.
- **Rubric:**
  1. *Appropriate Technique Choice* (50%) — Is the chosen method (drop/fill/investigate) sensible for each specific case?
  2. *Justification Quality* (50%) — Is the reasoning for each choice clearly and correctly explained?

---

### Activity 5.3 — Fixing Inconsistent Formatting
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.2 | **Prerequisites:** Activity 5.2
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn string cleaning methods (`.str.strip()`, `.str.lower()`, `.str.replace()`) and type conversion (`pd.to_numeric()`, `pd.to_datetime()`). Fellow fixes the branch name capitalization inconsistency, strips "XAF" from price fields and converts them to proper numeric type, and standardizes the three different date formats into one consistent datetime column.
- **Rubric:**
  1. *Correct Cleaning* (70%) — Are all formatting issues correctly and completely fixed?
  2. *Verification* (30%) — Does the fellow verify the fix worked (e.g., re-checking `.unique()` on branch names, `.dtypes` after conversion)?

---

### Activity 5.4 — Detecting & Handling Duplicates
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 5.3
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn `.duplicated()` and `.drop_duplicates()`, and the important nuance that "duplicate" isn't always obvious (exact row duplicates vs. likely-duplicate transactions with slightly different timestamps from a syncing glitch). Fellow identifies and removes true duplicates from Chapter 3, explaining how they distinguished real duplicates from legitimately similar-but-distinct transactions.
- **Rubric:**
  1. *Correct Identification* (60%) — Are true duplicates correctly found and removed, without over-deleting legitimate rows?
  2. *Reasoning Quality* (40%) — Is the duplicate-vs-legitimate distinction reasoning sound?

---

### Activity 5.5 — Outlier Detection & Treatment
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 5.3
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn outlier detection via the IQR method and boxplots (connecting back to z-scores from Milestone 3), and the critical judgment call: is an outlier a data entry error to fix/remove, or a real, meaningful event to keep (e.g., a genuine bulk-order day)? Fellow identifies outliers in Chapter 3's sales figures, investigates each one (checking related columns for clues), and makes and justifies a decision for each: fix, remove, or keep.
- **Rubric:**
  1. *Correct Detection* (40%) — Are outliers correctly identified via IQR/boxplot?
  2. *Investigation Quality* (30%) — Is each outlier genuinely investigated, not just flagged?
  3. *Decision Justification* (30%) — Is the fix/remove/keep decision well-reasoned per case?

---

### Activity 5.6 — Problem Solving 105: The Data Quality Audit
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.5 | **Prerequisites:** Activity 5.4, Activity 5.5
- **Evidence Required:** Written Submission
- **Prompt:** Fellow is given a *new*, never-before-seen small messy dataset (different from Nkwen Traders — e.g., a small public dataset with its own quirks) and must independently perform a full data quality audit: identify every issue, propose a cleaning plan for each (without necessarily executing all of it), and flag which issues they'd need to ask a stakeholder about before deciding how to handle (an important real-world instinct — not everything should be resolved unilaterally).
- **Rubric:**
  1. *Issue Identification* (40%) — Are real issues in the new dataset correctly found?
  2. *Cleaning Plan Quality* (35%) — Are proposed approaches sound?
  3. *Stakeholder Judgment* (25%) — Are genuinely ambiguous decisions correctly flagged for clarification rather than assumed?

---

### Activity 5.7 — Project: Nkwen Traders — From Messy to Clean
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 5.5 | **Prerequisites:** Activity 5.5, Activity 5.6
- **Evidence Required:** URL/Link (public Kaggle notebook)
- **Prompt:** Fellow produces a complete, end-to-end cleaning notebook for Nkwen Traders Chapter 3: every issue from the Activity 5.1 audit addressed, each decision documented in markdown with reasoning, and a final "before vs. after" comparison (row count, missing value count, data types) proving the cleaning worked. This cleaned dataset becomes the foundation for Milestones 6–8.
- **Rubric:**
  1. *Completeness* (35%) — Are all identified issues actually addressed?
  2. *Correctness* (30%) — Are the cleaning operations technically correct (no data corruption introduced)?
  3. *Documentation* (25%) — Is each decision clearly justified in markdown?
  4. *Before/After Verification* (10%) — Is the cleaning's success clearly demonstrated?

---

### Activity 5.8 — Brand: The Unsexy Truth About Data Science
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 5.7 | **Prerequisites:** Activity 5.7
- **Evidence Required:** URL/Link (article) **AND** Video Recording
- **Prompt:** Write an article and record a video on the "80% of data science is cleaning data" reality, using specific before/after examples from the Nkwen Traders cleaning project. This is a genuinely popular, relatable topic in the data community and a great portfolio piece showing real judgment, not just tool usage.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality?
  3. *Concrete Examples* (25%) — Are specific before/after examples used, not just abstract claims?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 5.9 — Mock Interview: Data Wrangling Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.6, Activity 5.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain how they decide between dropping/filling missing data, walk through an outlier decision from Activity 5.5, discuss when to flag a data issue to a stakeholder rather than deciding unilaterally, and reflect on the overall cleaning project.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 5 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 5.1 | Why Data Is Never Clean | learning | Beginner | 5 | Required |
| 5.2 | Handling Missing Data | learning | Beginner | 15 | Required |
| 5.3 | Fixing Inconsistent Formatting | learning | Intermediate | 15 | Required |
| 5.4 | Detecting & Handling Duplicates | learning | Intermediate | 15 | Required |
| 5.5 | Outlier Detection & Treatment | learning | Intermediate | 15 | Required |
| 5.6 | Problem Solving 105: The Data Quality Audit | learning | Advanced | 20 | Required |
| 5.7 | Project: Nkwen Traders — From Messy to Clean | project | Advanced | 25 | Required |
| 5.8 | The Unsexy Truth About Data Science (Brand) | blog_post | Intermediate | 15 | Required |
| 5.9 | Mock Interview: Data Wrangling Check | mock_interview | Advanced | 15 | Required |

**Milestone 5 total (if all completed):** 140 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–5)
- Milestone 1: 75 pts
- Milestone 2: 120 pts
- Milestone 3: 130 pts
- Milestone 4: 145 pts
- Milestone 5: 140 pts
- **Cumulative possible so far:** 610 pts

---

## Continuity Note
The cleaned dataset produced in Activity 5.7 becomes the canonical "Nkwen Traders — Clean" dataset used across Milestones 6 (Visualization), 7 (EDA Capstone-lite), and beyond — worth having fellows export/save their cleaned CSV in a consistent way (or the platform could provide an official "answer key" cleaned version to ensure everyone has consistent data for later Milestones, regardless of how well each fellow's own cleaning went).

---

## What's Next: Milestone 6 Preview
**Data Visualization & Storytelling** — matplotlib/seaborn, and the underrated skill of choosing the right chart for the right story, applied to the now-clean Nkwen Traders dataset. Say the word when ready.
