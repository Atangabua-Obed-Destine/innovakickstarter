# I-NNOVA KICKSTARTER — Data Science Track
## Milestone 7: Exploratory Data Analysis (EDA) Capstone-lite

**Status:** Draft for review
**Unlocks:** After Milestone 6 is fully completed
**Theme:** The first full "raw data → insights" project, tying Python, statistics, pandas, cleaning, and visualization (Milestones 2–6) into one cohesive, repeatable workflow. Deliberately uses a **new dataset outside Nkwen Traders** to test whether skills genuinely generalize, not just pattern-match to one familiar dataset. This is a checkpoint capstone — smaller in scope than the full Foundations Capstone at Milestone 16.

---

### Activity 7.1 — The EDA Framework: A Repeatable Process
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.7
- **Evidence Required:** Written Submission
- **Prompt:** Learn a structured, repeatable EDA framework (a checklist real analysts actually use): understand the business question → understand the data structure → assess quality/clean → univariate analysis (each variable alone) → bivariate/multivariate analysis (relationships) → summarize insights. Fellow writes this framework in their own words as a personal checklist they'll actually reuse.
- **Rubric:**
  1. *Completeness* (60%) — Are all key EDA stages correctly captured?
  2. *Personalization* (40%) — Is it written as a genuinely usable personal checklist, not just copied theory?

---

### Activity 7.2 — Choose Your Dataset & Define the Question
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 7.1 | **Prerequisites:** Activity 7.1
- **Evidence Required:** Written Submission
- **Prompt:** Fellow browses Kaggle Datasets and selects one public, real-world dataset (not Nkwen Traders — options might include public health data, education statistics, e-commerce sales, or similar; fellow's genuine interest matters here for motivation) with at least 500 rows and 6+ columns. Fellow writes 2–3 specific, answerable questions they intend to explore, applying the question-decomposition habit from Milestone 1.
- **Rubric:**
  1. *Dataset Suitability* (40%) — Is the chosen dataset appropriately sized/structured for a real EDA?
  2. *Question Quality* (60%) — Are the questions specific and genuinely answerable with the chosen data?

---

### Activity 7.3 — EDA Phase 1: Understand & Clean
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.2 | **Prerequisites:** Activity 7.2
- **Evidence Required:** URL/Link (public Kaggle notebook)
- **Prompt:** Applying Milestone 5's cleaning skills to an entirely new dataset (no hand-holding on what's "wrong" this time — fellow must independently discover the issues), fellow performs a full data quality audit and cleaning pass, documenting each decision in markdown.
- **Rubric:**
  1. *Issue Discovery* (35%) — Are real data quality issues independently and correctly identified?
  2. *Cleaning Correctness* (35%) — Are cleaning operations technically correct?
  3. *Documentation* (30%) — Is reasoning clearly documented for each decision?

---

### Activity 7.4 — EDA Phase 2: Univariate & Bivariate Analysis
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.3 | **Prerequisites:** Activity 7.3
- **Evidence Required:** URL/Link (public Kaggle notebook)
- **Prompt:** Applying Milestones 3 and 6's skills, fellow analyzes each key variable individually (distributions, summary stats) and then explores relationships between variables relevant to their Activity 7.2 questions (correlations, group comparisons, scatter plots as appropriate) — always choosing chart/stat types deliberately, per the Milestone 6 framework, not defaulting to the same chart for everything.
- **Rubric:**
  1. *Univariate Analysis Quality* (30%) — Are individual variables appropriately analyzed?
  2. *Bivariate Analysis Quality* (35%) — Are relevant relationships correctly explored with appropriate techniques?
  3. *Technique Appropriateness* (20%) — Is chart/stat choice well-matched to each variable type and question?
  4. *Progress Toward Questions* (15%) — Does the analysis clearly work toward answering the Activity 7.2 questions?

---

### Activity 7.5 — EDA Phase 3: Insights & Recommendations
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.4 | **Prerequisites:** Activity 7.4
- **Evidence Required:** URL/Link (public Kaggle notebook) **AND** Presentation/Slide Deck
- **Prompt:** Fellow synthesizes Phases 1–2 into a final, polished notebook with a clear "Key Insights" section directly answering the Activity 7.2 questions, appropriately hedged (what the data supports vs. what would need more investigation — foreshadowing the correlation-vs-causation caution from Milestone 8), plus a short slide deck (5–8 slides) presenting the findings to a general audience.
- **Rubric:**
  1. *Insight Quality* (35%) — Are the insights genuinely supported by the analysis, not overstated?
  2. *Question Resolution* (25%) — Are the original Activity 7.2 questions clearly and directly addressed?
  3. *Appropriate Hedging* (20%) — Does the fellow correctly distinguish supported conclusions from speculation?
  4. *Presentation Quality* (20%) — Is the slide deck genuinely presentable and well-structured?

---

### Activity 7.6 — Collaborate: Peer EDA Review
- **Type:** `project`
- **Pillar:** Collaborate
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.5
- **Evidence Required:** Written Submission
- **Prompt:** Fellow reviews another fellow's (or a mentor-provided sample) EDA notebook from Activity 7.5, leaving substantive feedback: at least one genuine strength, one place where a conclusion seems overstated or under-supported, and one alternative chart/technique that might have revealed something different. This builds the critical-reading skill of evaluating others' analysis — a core, underrated data science skill (data scientists review each other's work constantly).
- **Rubric:**
  1. *Feedback Substance* (60%) — Is the feedback specific and genuinely useful, not generic praise?
  2. *Technical Accuracy* (40%) — Is the critique technically sound (correctly identifies real issues or genuine strengths)?

---

### Activity 7.7 — Brand: My First End-to-End EDA
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 7.5 | **Prerequisites:** Activity 7.5, Activity 7.6
- **Evidence Required:** URL/Link (article) **AND** Video Recording
- **Prompt:** Write a polished article and record a video walking through the complete EDA journey — dataset choice, questions asked, cleaning challenges, key insights, and final recommendation. This is the most substantial portfolio piece yet in the track — treat it as a genuine "look what I can do end-to-end" showcase.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, professionally structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality?
  3. *End-to-End Narrative* (25%) — Does it genuinely tell the full journey, not just show final charts?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 7.8 — Mock Interview: EDA Walkthrough
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.6, Activity 7.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 72/100
- **Required Sessions:** 1
- **Focus:** Walk through the full EDA process from dataset choice to final recommendation, explain key decisions (why this chart, why this cleaning approach), and respond to a "what would you do differently" style follow-up.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 7 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 7.1 | The EDA Framework: A Repeatable Process | learning | Intermediate | 15 | Required |
| 7.2 | Choose Your Dataset & Define the Question | learning | Intermediate | 10 | Required |
| 7.3 | EDA Phase 1: Understand & Clean | project | Advanced | 25 | Required |
| 7.4 | EDA Phase 2: Univariate & Bivariate Analysis | project | Advanced | 25 | Required |
| 7.5 | EDA Phase 3: Insights & Recommendations | project | Advanced | 25 | Required |
| 7.6 | Collaborate: Peer EDA Review | project | Intermediate | 15 | Required |
| 7.7 | My First End-to-End EDA (Brand) | blog_post | Advanced | 20 | Required |
| 7.8 | Mock Interview: EDA Walkthrough | mock_interview | Advanced | 15 | Required |

**Milestone 7 total (if all completed):** 150 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–7)
- Milestones 1–6: 750 pts
- Milestone 7: 150 pts
- **Cumulative possible so far:** 900 pts

---

## Operational Note
Activity 7.6 (Peer EDA Review) needs the same pairing/matching infrastructure flagged for the Software Engineering track's collaborative Milestone — worth solving once at the platform level rather than per-track, since all three tracks now have at least one peer-review-dependent activity.

---

## What's Next: Milestone 8 Preview
**Applied Statistics & Probability II (Inferential Statistics)** — hypothesis testing, confidence intervals, and correlation vs. causation, arguably the single most misused concept in the entire field and worth its own deep, dedicated treatment before any machine learning begins. Say the word when ready.
