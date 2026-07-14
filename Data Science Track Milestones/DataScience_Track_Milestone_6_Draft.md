# I-NNOVA KICKSTARTER — Data Science Track
## Milestone 6: Data Visualization & Storytelling

**Status:** Draft for review
**Unlocks:** After Milestone 5 is fully completed
**Theme:** matplotlib/seaborn, and the underrated, genuinely differentiating skill of choosing the right chart for the right story — applied to the now-clean Nkwen Traders dataset. A bad chart can mislead just as easily as bad statistics; this Milestone treats visualization as a communication discipline, not just a technical one.
**Target fellow:** Has a cleaned Nkwen Traders dataset from Milestone 5 (using the platform's official cleaned version if their own cleaning had gaps).

---

### Activity 6.1 — Why Visualization Matters (and How It Lies)
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 5.7
- **Evidence Required:** Written Submission
- **Prompt:** Fellow researches common chart manipulation techniques (truncated y-axes exaggerating differences, cherry-picked time ranges, misleading pie charts with too many slices, 3D charts distorting proportions) and finds one real published example (news, business report) exhibiting at least one of these issues, explaining what makes it misleading and how they'd fix it.
- **Rubric:**
  1. *Technique Understanding* (60%) — Are misleading techniques correctly identified and explained?
  2. *Applied Critique* (40%) — Is the found real example correctly analyzed and a fix proposed?

---

### Activity 6.2 — Matplotlib Fundamentals
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.1 | **Prerequisites:** Activity 6.1
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn matplotlib basics: line plots, bar charts, histograms, titles/labels/legends, and figure sizing. Fellow creates 4 basic charts from the cleaned Nkwen Traders data (a line plot of monthly revenue trend, a bar chart of revenue by branch, a histogram of transaction values, and one chart of their choice), each properly labeled and titled — no unlabeled "mystery charts."
- **Rubric:**
  1. *Chart Correctness* (50%) — Do all 4 charts correctly represent the underlying data?
  2. *Labeling & Clarity* (50%) — Are titles, axis labels, and legends present and clear on every chart?

---

### Activity 6.3 — Seaborn for Statistical Visualization
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.2 | **Prerequisites:** Activity 6.2
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn seaborn's higher-level statistical plots: boxplots (revisiting outliers from Milestone 5), violin plots, and a correlation heatmap across numeric columns. Fellow creates a boxplot comparing transaction value distributions across branches and a heatmap showing correlations between numeric columns (e.g., price, quantity, revenue), writing a short interpretation of what the heatmap reveals.
- **Rubric:**
  1. *Correct Plotting* (50%) — Are boxplot and heatmap correctly generated?
  2. *Interpretation* (50%) — Is the correlation interpretation correct (and appropriately cautious about correlation ≠ causation, foreshadowing Milestone 8)?

---

### Activity 6.4 — Choosing the Right Chart
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.3
- **Evidence Required:** Written Submission
- **Prompt:** Learn a practical decision framework: comparison → bar chart, trend over time → line chart, distribution → histogram/boxplot, relationship between two variables → scatter plot, part-to-whole → pie chart (used sparingly, only for few categories), correlation across many variables → heatmap. Given 6 different Nkwen Traders business questions, fellow selects and justifies the correct chart type for each, without necessarily building all of them.
- **Rubric:**
  1. *Correct Chart Selection* (70%) — Is the right chart type chosen for each scenario?
  2. *Justification Quality* (30%) — Is the reasoning for each choice sound?

---

### Activity 6.5 — Multi-Panel Dashboards
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.4 | **Prerequisites:** Activity 6.4
- **Evidence Required:** URL/Link (Kaggle/Colab notebook)
- **Prompt:** Learn `plt.subplots()` to combine multiple charts into a single cohesive dashboard-style figure. Fellow builds a 4-panel figure (e.g., revenue trend, branch comparison, product category breakdown, transaction value distribution) with a shared title, consistent styling, and appropriate sizing — practicing the layout skills needed for real stakeholder-facing dashboards.
- **Rubric:**
  1. *Layout Correctness* (40%) — Are all 4 panels correctly arranged and functional?
  2. *Visual Consistency* (30%) — Consistent styling/sizing across panels?
  3. *Content Selection* (30%) — Do the 4 chosen charts together tell a coherent story?

---

### Activity 6.6 — Problem Solving 106: Fix the Bad Chart
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 6.4
- **Evidence Required:** URL/Link (Kaggle/Colab notebook) + Written Submission
- **Prompt:** Fellow is given (or generates) 2–3 deliberately bad charts of Nkwen Traders data (wrong chart type for the data, unlabeled axes, misleading truncated y-axis, too many pie slices) and must diagnose what's wrong with each and rebuild a corrected version, writing a short note explaining the specific fix for each.
- **Rubric:**
  1. *Correct Diagnosis* (40%) — Are the chart problems correctly identified?
  2. *Correct Rebuild* (40%) — Are the corrected charts genuinely improved and accurate?
  3. *Explanation Clarity* (20%) — Is the reasoning for each fix clearly communicated?

---

### Activity 6.7 — Project: Nkwen Traders Visual Story
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 6.5 | **Prerequisites:** Activity 6.5, Activity 6.6
- **Evidence Required:** URL/Link (public Kaggle notebook) **AND** Presentation/Slide Deck
- **Prompt:** Fellow selects one genuine business question about Nkwen Traders (their own choice — e.g., "should management open a 5th branch, and where?") and builds both a supporting analysis notebook AND a short slide deck (5–8 slides) presenting the visual story to a non-technical management audience — charts chosen deliberately, narrative flow, one clear recommendation at the end.
- **Rubric:**
  1. *Chart Selection* (25%) — Are chart types well-matched to the story being told?
  2. *Narrative Flow* (25%) — Does the deck build logically toward a clear conclusion?
  3. *Business Relevance* (25%) — Is the final recommendation genuinely actionable and well-supported?
  4. *Presentation Polish* (25%) — Is the deck genuinely presentable to a real audience (not just raw charts pasted in)?

---

### Activity 6.8 — Brand: How I Turned Data Into a Story
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 6.7 | **Prerequisites:** Activity 6.7
- **Evidence Required:** URL/Link (article) **AND** Video Recording
- **Prompt:** Write an article and record a video walking through the Visual Story project — the business question, why specific charts were chosen over alternatives, and the final recommendation. Essentially a public "present the deck" moment, great interview and portfolio practice simultaneously.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, reasonable production quality?
  3. *Storytelling Quality* (25%) — Does the narrative genuinely build toward the conclusion?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 6.9 — Mock Interview: Visualization & Communication Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Intermediate
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.6, Activity 6.7
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain when to use different chart types, how charts can mislead, walk through their Visual Story presentation choices, and defend their final recommendation as if presenting to real management.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 6 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 6.1 | Why Visualization Matters (and How It Lies) | learning | Beginner | 10 | Required |
| 6.2 | Matplotlib Fundamentals | learning | Beginner | 15 | Required |
| 6.3 | Seaborn for Statistical Visualization | learning | Intermediate | 15 | Required |
| 6.4 | Choosing the Right Chart | learning | Intermediate | 15 | Required |
| 6.5 | Multi-Panel Dashboards | project | Intermediate | 20 | Required |
| 6.6 | Problem Solving 106: Fix the Bad Chart | learning | Intermediate | 15 | Required |
| 6.7 | Project: Nkwen Traders Visual Story | project | Advanced | 25 | Required |
| 6.8 | How I Turned Data Into a Story (Brand) | blog_post | Intermediate | 15 | Required |
| 6.9 | Mock Interview: Visualization & Communication Check | mock_interview | Intermediate | 10 | Required |

**Milestone 6 total (if all completed):** 140 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–6)
- Milestone 1: 75 pts
- Milestone 2: 120 pts
- Milestone 3: 130 pts
- Milestone 4: 145 pts
- Milestone 5: 140 pts
- Milestone 6: 140 pts
- **Cumulative possible so far:** 750 pts

---

## What's Next: Milestone 7 Preview
**Exploratory Data Analysis (EDA) Capstone-lite** — the first full "raw data → insights" project tying Milestones 2–6 together into one cohesive workflow, before moving into inferential statistics and eventually machine learning. This is a checkpoint capstone, not the full Foundations Capstone (that's Milestone 16). Say the word when ready.
