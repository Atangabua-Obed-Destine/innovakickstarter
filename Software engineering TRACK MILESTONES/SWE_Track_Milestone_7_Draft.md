# I-NNOVA KICKSTARTER — Software Engineering Track
## Milestone 7: Databases — Persisting Real Data (MySQL)

**Status:** Draft for review
**Unlocks:** After Milestone 6 is fully completed
**Theme:** Relational database fundamentals and connecting a Node/Express API to MySQL, so data survives beyond a single server run. Directly echoes real schema-design work (e.g., ScholarTrack/EduTrustISMS, POS product data).
**Target fellow:** Has built an in-memory REST API in Express; has never designed or queried a database before.

---

### Activity 7.1 — Why Databases Exist
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 6.7
- **Evidence Required:** Text Response
- **Resources:** https://www.youtube.com/watch?v=HXV3zeQKqGY, https://www.freecodecamp.org/news/learn-sql-free-relational-database-courses-for-beginners/
- **Prompt:** Fellow reflects on the limitation of their Milestone 6 API (data vanishing on restart) and researches what a database actually solves. Write a short explanation covering persistence, and the basic idea of tables/rows/columns vs. the arrays/objects they've used so far.
- **Rubric:**
  1. *Problem Understanding* (50%) — Do they correctly identify why in-memory storage falls short?
  2. *Conceptual Accuracy* (50%) — Correct basic grasp of tables/rows/columns?

---

### Activity 7.2 — MySQL Setup & Your First Table
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 10
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.1
- **Evidence Required:** File Upload (screenshot)
- **Resources:** https://www.youtube.com/watch?v=HXV3zeQKqGY, https://dev.mysql.com/doc/mysql-getting-started/en/
- **Prompt:** Install MySQL locally (or use a free hosted instance) and a client (MySQL Workbench, TablePlus, or CLI). Create a database and a single table (e.g., `fellows` with `id`, `name`, `email`, `points`) with appropriate data types and a primary key. Screenshot showing the table structure and one inserted row.
- **Rubric:**
  1. *Correct Table Design* (60%) — Sensible column names, appropriate data types, primary key set?
  2. *Successful Setup* (40%) — Is the database running and the row visible?

---

### Activity 7.3 — CRUD with SQL
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 7.2 | **Prerequisites:** Activity 7.2
- **Evidence Required:** Text Response (SQL queries + results)
- **Resources:** https://www.youtube.com/watch?v=HXV3zeQKqGY, https://dev.mysql.com/doc/refman/8.0/en/select.html
- **Prompt:** Learn `SELECT`, `INSERT`, `UPDATE`, `DELETE`, plus `WHERE`, `ORDER BY`, and `LIMIT`. Fellow writes and runs at least 8 queries against their `fellows` table (insert several rows, select with filters, update a value, delete a row) and submits the queries alongside their results.
- **Rubric:**
  1. *Query Correctness* (70%) — Do all queries run correctly and return expected results?
  2. *Variety* (30%) — Is a reasonable range of clauses/operations demonstrated?

---

### Activity 7.4 — Relationships & Normalization
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.3 | **Prerequisites:** Activity 7.3
- **Evidence Required:** File Upload (ER diagram, PDF or image)
- **Resources:** https://www.youtube.com/watch?v=HXV3zeQKqGY, https://www.freecodecamp.org/news/learn-relational-database-design/
- **Prompt:** Learn primary/foreign keys, one-to-many and many-to-many relationships, and basic normalization (avoiding repeated/redundant data). Fellow designs an ER diagram for a small multi-table scenario (e.g., "fellows" and "activities they've completed" — a many-to-many via a join table), then implements it as actual tables in MySQL with foreign keys.
- **Rubric:**
  1. *Relationship Correctness* (40%) — Are relationships modeled correctly (right cardinality, proper join table if needed)?
  2. *Normalization* (30%) — Is redundant data avoided appropriately?
  3. *Implementation* (30%) — Do the actual MySQL tables match the diagram with working foreign keys?

---

### Activity 7.5 — Connecting Express to MySQL
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.4 | **Prerequisites:** Activity 7.4, Activity 6.5
- **Evidence Required:** URL/Link (GitHub repo)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://www.npmjs.com/package/mysql2
- **Prompt:** Install a MySQL driver (e.g., `mysql2`), connect it to an Express app, and convert the Milestone 6 in-memory `GET /fellows` and `POST /fellows` routes to actually read from and write to the real MySQL table from 7.2, including basic protection against SQL injection (parameterized queries, not string concatenation).
- **Rubric:**
  1. *Correct Integration* (50%) — Do the routes correctly read/write real database data?
  2. *Security Practice* (30%) — Are parameterized queries used (no raw string-concatenated SQL)?
  3. *Error Handling* (20%) — Are DB connection/query errors handled gracefully (not crashing the server)?

---

### Activity 7.6 — Problem Solving 107: Schema Design Challenge
- **Type:** `learning`
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 4 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 7.4
- **Evidence Required:** File Upload (ER diagram) + Text Response
- **Resources:** https://www.youtube.com/watch?v=HXV3zeQKqGY, https://www.freecodecamp.org/news/learn-relational-database-design/
- **Prompt:** Given a realistic scenario (e.g., "design the database for a school management system tracking students, classes, and grades" — deliberately close to real EduTrustISMS-style problems), fellow designs a normalized schema with at least 4 related tables, explains their key design decisions (why certain relationships are one-to-many vs. many-to-many), and flags any tradeoffs they considered.
- **Rubric:**
  1. *Schema Correctness* (40%) — Are tables, keys, and relationships correctly designed?
  2. *Normalization* (30%) — Is the schema reasonably normalized without being needlessly over-engineered?
  3. *Reasoning Quality* (30%) — Are design decisions clearly explained?

---

### Activity 7.7 — Project: Full CRUD API with Persistent Storage
- **Type:** `project`
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 6 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 7.5 | **Prerequisites:** Activity 7.5, Activity 7.6
- **Evidence Required:** URL/Link (GitHub repo, with README + schema diagram)
- **Resources:** https://www.youtube.com/watch?v=Oe421EPjeBE, https://www.npmjs.com/package/mysql2
- **Prompt:** Rebuild the Milestone 6 REST API project (or extend it) so all data is fully persisted in MySQL, with at least two related tables (e.g., fellows and their completed activities), full CRUD across both, and parameterized queries throughout. Include the schema diagram in the README.
- **Rubric:**
  1. *Full CRUD with Real Persistence* (40%) — Does everything actually persist correctly across server restarts?
  2. *Relational Design* (25%) — Are the two+ tables correctly related and queried (e.g., joins where appropriate)?
  3. *Security & Error Handling* (20%) — Parameterized queries, graceful failure handling?
  4. *Documentation* (15%) — Clear README with schema diagram and route list?

---

### Activity 7.8 — Brand: From Memory to Database
- **Type:** `blog_post`
- **Pillar:** Brand
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 2 days | **Late Penalty:** 0%
- **Chain Parent:** Activity 7.7 | **Prerequisites:** Activity 7.7
- **Evidence Required:** URL/Link (LinkedIn post)
- **Resources:** https://www.youtube.com/watch?v=i0PYPYZJFh8, https://www.freecodecamp.org/news/linkedin-handbook-get-your-first-dev-job/
- **Prompt:** Post a short LinkedIn writeup on the journey from in-memory data (Milestone 6) to a real persistent MySQL-backed API — what changed, what was hardest, and a peek at their schema diagram. Same tagging convention as prior Milestones.
- **Rubric:**
  1. *Authenticity & Effort* (50%) — Genuine explanation, not just a link drop.
  2. *Technical Accuracy* (50%) — Correctly describes the shift from in-memory to persistent storage.

---

### Activity 7.9 — Mock Interview: Database Fundamentals Check
- **Type:** `mock_interview`
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.6, Activity 7.7
- **Interview Mode:** AI Interview
- **Passing Score:** 70/100
- **Required Sessions:** 1
- **Focus:** Explain normalization, primary vs. foreign keys, why parameterized queries matter (SQL injection), and a walkthrough of their schema design decisions from Activity 7.6/7.7.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 7 Summary
| # | Activity | Type | Difficulty | Points | Required? |
|---|----------|------|------------|--------|-----------|
| 7.1 | Why Databases Exist | learning | Beginner | 5 | Required |
| 7.2 | MySQL Setup & Your First Table | project | Beginner | 10 | Required |
| 7.3 | CRUD with SQL | learning | Beginner | 15 | Required |
| 7.4 | Relationships & Normalization | learning | Intermediate | 15 | Required |
| 7.5 | Connecting Express to MySQL | learning | Intermediate | 15 | Required |
| 7.6 | Problem Solving 107: Schema Design Challenge | learning | Intermediate | 15 | Required |
| 7.7 | Full CRUD API with Persistent Storage | project | Advanced | 25 | Required |
| 7.8 | From Memory to Database (Brand) | blog_post | Beginner | 5 | Optional |
| 7.9 | Mock Interview: Database Fundamentals Check | mock_interview | Advanced | 15 | Required |

**Milestone 7 total (if all completed):** 120 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–7)
- Milestone 1: 70 pts
- Milestone 2: 90 pts
- Milestone 3: 85 pts
- Milestone 4: 95 pts
- Milestone 5: 90 pts
- Milestone 6: 105 pts
- Milestone 7: 120 pts
- **Cumulative possible so far:** 655 pts

---

## What's Next
- **Milestone 8 (planned):** Git Branching & Real-World Collaboration — branches, merge conflicts, pull requests, code review etiquette. Fellows have only made single-branch commits so far; this closes that gap before team-based work becomes relevant.
- **Milestone 9 (planned):** Authentication & Authorization — password hashing, sessions/JWT, protecting routes. Needed before any "real" full-stack app can safely handle user accounts.

Let me know if you want Milestone 8 drafted next in that order, or want to reprioritize.
