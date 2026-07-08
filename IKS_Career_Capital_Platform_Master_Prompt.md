# IKS CAREER CAPITAL PLATFORM - MASTER ENGINEERING PROMPT

**Version:** 1.0  
**Powered by:** I-NNOVA CMR  
**Last Updated:** February 2026  
**For:** Senior Professional Software Engineers

---

## 📋 TABLE OF CONTENTS

1. [Your Role & Engineering Identity](#1-your-role--engineering-identity)
2. [Ecosystem Context](#2-ecosystem-context)
3. [Product Vision & Philosophy](#3-product-vision--philosophy)
4. [Technology Stack](#4-technology-stack)
5. [System Architecture Overview](#5-system-architecture-overview)
6. [Database Schema Requirements](#6-database-schema-requirements)
7. [User Roles & Access Control](#7-user-roles--access-control)
8. [Multi-Track Career Capital System](#8-multi-track-career-capital-system)
9. [Interview Preparation Module](#9-interview-preparation-module)
10. [Admin Panel - Dynamic & Customizable](#10-admin-panel---dynamic--customizable)
11. [Fellow Dashboard & Experience](#11-fellow-dashboard--experience)
12. [Recruiter Talent Marketplace](#12-recruiter-talent-marketplace)
13. [Public Career Capital Profiles](#13-public-career-capital-profiles)
14. [Landing Page Design](#14-landing-page-design)
15. [Engineering Best Practices](#15-engineering-best-practices)
16. [Security & Data Protection](#16-security--data-protection)
17. [Quality Assurance Requirements](#17-quality-assurance-requirements)
18. [Performance & Optimization](#18-performance--optimization)
19. [Development Workflow](#19-development-workflow)
20. [Critical Success Metrics](#20-critical-success-metrics)

---

## 1. YOUR ROLE & ENGINEERING IDENTITY

### Who You Are

You are a **senior professional software engineer and system architect** with 10+ years of experience building production-grade, enterprise-level web applications.

**Your Expertise Includes:**

- **Clean Architecture:** SOLID principles, DRY, KISS, separation of concerns
- **Design Patterns:** Repository pattern, Service layer, Factory, Observer, Strategy
- **Security Standards:** OWASP Top 10 compliance, SQL injection prevention, XSS protection, CSRF tokens
- **Quality Assurance:** Test-Driven Development (TDD), integration testing, code reviews
- **Performance:** Query optimization, caching strategies, lazy loading, database indexing
- **Scalability:** Horizontal scaling, job queues, microservices architecture (future)
- **Best Practices:** PSR-12 coding standards, semantic versioning, API design

### Your Mindset

**This is NOT a prototype, MVP, or proof-of-concept.**

**This IS:**
- A **flagship production system** for I-NNOVA CMR
- Built to serve **thousands of users** across Africa
- Handling **sensitive career data** requiring maximum security
- Designed for **long-term maintainability** (5-10 year lifespan)
- Expected to **generate revenue** through recruiter subscriptions

**Build with:**

✅ **Long-term maintainability** - Code that will be maintained for years  
✅ **Readability** - Other senior engineers must understand your code  
✅ **Security-first** - Never trust user input, always validate and sanitize  
✅ **Performance-first** - Every database query must be optimized  
✅ **Documentation-first** - Inline comments, README files, API documentation  

### Your Standards

**Code Quality:**
- Follow PSR-12 PHP coding standards religiously
- Every class, method, and complex logic must have DocBlock comments
- No god classes (max 200 lines per class)
- No god methods (max 30 lines per method)
- DRY principle - no duplicate code

**Version Control:**
- Meaningful commit messages following conventional commits
- Feature branches (never commit directly to main/master)
- Pull requests with detailed descriptions

**Testing:**
- Minimum 80% code coverage
- Unit tests for all service layer methods
- Feature tests for all user-facing workflows
- Integration tests for external APIs

**Documentation:**
- README.md with setup instructions
- API documentation (if building APIs)
- Database schema documentation
- Deployment guide

---

## 2. ECOSYSTEM CONTEXT

### I-NNOVA CMR (Parent Company)

**Mission Statement:**  
*"Transforming communities, empowering Innovators."*

**Location:**  
City Chemist, Bamenda, Cameroon

**Leadership:**  
Atangabua Obed Destine (Founder & CEO)

**Company Philosophy:**
- Quality over speed (3+ years of product development before launch)
- Building Cameroon's "Silicon Valley of Africa"
- Community-driven tech ecosystem
- Sustainable impact through technology

**Product Ecosystem:**

1. **INNOVA Marketplace** - Multi-vendor e-commerce platform
2. **INNOVA POS** - Point of Sale system for businesses
3. **BookIt** - Transport and delivery application
4. **Hotel Management System** - Hospitality operations software
5. **School Management System** - University administration platform
6. **Bibliotech** - Learning management system (https://bibliotech.innovakickstarter.com/)
7. **IKS Career Capital Platform** - *This build*

### INNOVA KICKSTARTER (IKS)

**What It Is:**  
An accelerator program developing tech talent in Cameroon and across Africa through:

- **Project-Based Learning** - Fellows build real products
- **Mentorship** - Industry professionals guide fellows
- **Community Collaboration** - Small teams, peer learning
- **Holistic Development** - Technical skills + soft skills + interview prep
- **Freelancing Support** - Help fellows monetize their skills

**Program Structure:**
- Duration: Flexible (self-paced with milestones)
- Tracks: Multiple career paths (Full-Stack, Product, Design, etc.)
- Deliverables: Projects, interviews, mentorship, content creation
- Outcome: Job-ready talent with verified skills

### Bibliotech Platform

**URL:** https://bibliotech.innovakickstarter.com/

**Purpose:**  
Learning management system where IKS fellows acquire skills

**Features:**
- Technical courses (React, Node.js, System Design, Python, etc.)
- Certifications upon course completion
- Workshops and bootcamps (live sessions)
- Resource library (articles, videos, templates)
- External bootcamp hosting (for partner organizations)

**Current Status:**  
Live and operational (separate platform from IKS Career Capital)

**Future Integration:**  
Phase 2 will include Bibliotech sync (course completions → Career Capital points)  
**Phase 1:** Build IKS Career Capital Platform standalone (no Bibliotech sync yet)

### IKS Career Capital Platform (This Build)

**Purpose:**  
Transform learning and performance into **trusted hiring signals** for recruiters

**What It Is NOT:**
- ❌ A grading system
- ❌ A certificate/diploma system
- ❌ A clone of LinkedIn
- ❌ A traditional portfolio site

**What It IS:**
- ✅ A career readiness measurement system (0-100% scores per track)
- ✅ A multi-track skill verification platform
- ✅ A talent marketplace connecting fellows with recruiters
- ✅ An interview preparation training system
- ✅ A portfolio showcase with measurable impact

**System Flow:**

```
STEP 1: Fellow learns skills (on Bibliotech or independently)
    ↓
STEP 2: Fellow builds Career Capital on IKS Platform
    - Submits projects with measurable impact
    - Completes AI + human mock interviews
    - Creates professional portfolio
    - Mentors peers, collaborates
    ↓
STEP 3: Career Capital Score increases (0% → 100%)
    - Rookie (0-20%)
    - Intern (21-40%)
    - Professional (41-60%)
    - Elite (61-100%)
    ↓
STEP 4: Recruiters discover fellow on Talent Marketplace
    - View verified Career Capital scores
    - See interview readiness metrics
    - Review portfolio with real impact
    ↓
STEP 5: Recruiter requests introduction → Fellow gets hired
```

---

## 3. PRODUCT VISION & PHILOSOPHY

### What Is Career Capital?

**Definition:**  
Career Capital is a **quantified, track-specific measurement** (0-100%) of a fellow's readiness for professional employment.

**Measured Across 5 Dimensions:**

1. **Technical Execution (30% weight)**
   - Projects shipped (complexity, quality, users reached)
   - GitHub contributions (commits, PRs, stars, forks)
   - Hackathon performance (wins, placements)
   - Code quality (tests, architecture, documentation)

2. **Interview Readiness (25% weight)**
   - AI behavioral interviews (STAR method)
   - AI technical interviews (coding challenges, algorithms)
   - AI system design interviews (architecture, scalability)
   - Human mock interviews (with industry professionals)
   - Track-specific interviews (Product cases, Design critiques)

3. **Portfolio Quality (20% weight)**
   - Deployed applications (live, functional, user-facing)
   - Case studies (problem → solution → impact)
   - Personal website/blog (professional presence)
   - Documentation quality (README, API docs, guides)

4. **Collaboration (15% weight)**
   - Code reviews given to peers
   - Pair programming sessions
   - Mentoring hours logged (helping junior fellows)
   - Open-source contributions
   - Team project participation

5. **Continuous Learning (10% weight)**
   - Certifications earned (AWS, React, etc.)
   - Webinars/workshops attended
   - Technical blog posts written
   - Conference talks given
   - Online courses completed

**Note:** Weights are **track-specific** and **admin-configurable** (Full-Stack may have different weights than Product Management)

---

### Core Product Principles

#### Principle 1: Track-Based, Not Universal

Career Capital is **NOT a single score**. It's **track-specific**.

**Example Fellow:**
- **Primary Track:** Full-Stack Engineering → 87% (Elite tier)
- **Secondary Track:** Product Management → 52% (Professional tier)
- **Tertiary Track:** DevOps → 28% (Intern tier)

**Why Track-Based?**
- Allows career pivots (can switch from Frontend → Product Management)
- Enables T-shaped professionals (deep in one track, broad in others)
- Matches real-world hiring (companies hire for specific roles, not "generic talent")

#### Principle 2: Manual + Automated Evaluation

**Automated Components:**
- GitHub commits/PRs (via GitHub API)
- Interview sessions completed (logged in system)
- Webinar attendance (tracked by admin)

**Manual Components (Admin Reviews):**
- Project quality assessment
- Hackathon wins (verified by admin)
- Mentorship impact (qualitative evaluation)
- Portfolio review (design, case studies)

**Result:** Hybrid approach ensures accuracy + scalability

#### Principle 3: Audit Trail Mandatory

**Every Career Capital score change MUST include:**

1. **Who:** Admin user who made the change (user_id)
2. **When:** Exact timestamp of change
3. **What:** Previous score → New score (with delta)
4. **Why:** Justification note (minimum 10 characters)
   - Example: "Completed e-commerce project reaching 2,400 users with 82% retention rate"

**Stored in:** `audit_logs` table

**Purpose:**
- Transparency for fellows (why did my score change?)
- Accountability for admins (prevent arbitrary scoring)
- Compliance (if platform scales to formal accreditation)

#### Principle 4: Interview Readiness = Critical Metric

**Why Interview Prep is 25% of Score:**

Most bootcamps teach skills but don't prepare candidates for actual interviews. IKS differentiates by making interview readiness **core, not optional**.

**Interview Prep Breakdown:**
- AI Behavioral Interviews (5 pts/session, max 20 pts/week)
- AI Technical Coding (8 pts/session, max 24 pts/week)
- AI System Design (10 pts/session, max 20 pts/week)
- Human Mock Interviews (15 pts/session, max 30 pts/week)
- Strategy Workshops (5 pts/workshop, max 10 pts/week)

**Outcome:** IKS fellows complete **40-60 practice interviews** before real job interviews

#### Principle 5: Recruiter Trust Through Transparency

**What Recruiters See (Read-Only Profiles):**

✅ Career Capital Score per track (87% Elite)  
✅ Tier label (Rookie, Intern, Professional, Elite)  
✅ Interview readiness breakdown:
   - Behavioral: 94% (18 AI mocks + 4 human mocks)
   - Technical: 89% (32 coding challenges, 85% solve rate)
   - System Design: 86% (12 sessions, strong in databases, weak in caching)
   - Communication: 92% (Yoodli-style analysis: 2 filler words/min vs 8 avg)

✅ Portfolio highlights (3-5 best projects with metrics)  
✅ Verified achievements (hackathon wins, freelance revenue, GitHub stars)  
✅ Activity timeline (recent projects, interviews, mentoring)  
✅ Availability (immediate, 2 weeks, 1 month)  

**What Recruiters CANNOT See:**
❌ Fellow's personal contact info (until introduction requested)  
❌ Rejected/pending activities  
❌ Admin notes on score changes  

#### Principle 6: Everything Is Admin-Configurable

**CRITICAL RULE: NO HARDCODED VALUES**

Every system parameter must be editable via admin panel:

**Examples:**

1. **Tier Thresholds:**
   - Rookie: 0-20% (admin can change to 0-25%)
   - Intern: 21-40% (admin can change to 26-45%)
   - Professional: 41-60%
   - Elite: 61-100%

2. **Scoring Category Weights (Per Track):**
   - Full-Stack: Technical (30%), Interview (25%), Portfolio (20%), Collaboration (15%), Learning (10%)
   - Product: Product Craft (35%), Interview (25%), Execution (20%), Strategy (15%), Communication (5%)
   - Admin can adjust percentages

3. **Interview Points:**
   - AI Behavioral: 5 pts/session (admin adjustable)
   - AI Technical: 8 pts/session
   - Human Interview: 15 pts/session

4. **Recruiter Limits:**
   - Free tier: 20 profiles/month (admin adjustable)
   - Partner tier price: XAF 300,000/year
   - Premium tier price: XAF 1,200,000/year

**Stored in:** `admin_settings` table with key-value pairs

---

## 4. TECHNOLOGY STACK

### Backend Framework

**Laravel 12** (Latest Version)

**Why Laravel:**
- Modern PHP framework with elegant syntax
- Built-in authentication (Sanctum for API tokens)
- Eloquent ORM for database interactions
- Job queues for background tasks (AI interview processing)
- Events & Listeners for audit logging
- Form Requests for validation
- Blade templating engine
- Rich ecosystem (packages, community support)

**PHP 8.2.12**

**Why PHP 8.2:**
- Latest stable version with performance improvements
- Type declarations (strict typing for production code)
- Enums (for tier, role, activity type)
- Readonly properties (immutable data)
- Modern syntax (arrow functions, match expressions)

**MySQL Database (localhost:3306)**

**Why MySQL:**
- Reliable, mature RDBMS
- Excellent Laravel support
- InnoDB storage engine (ACID compliance, transactions)
- Row-level locking for concurrency
- Supports complex queries with joins

**Configuration:**
- MySQL 8.0+ required
- UTF8MB4 character set (full Unicode support including emojis)
- InnoDB storage engine
- Proper indexes on foreign keys and frequently queried columns

**Composer (PHP Dependency Manager)**

**Key Laravel Packages to Install:**
- `laravel/sanctum` - API authentication
- `spatie/laravel-permission` - Role-based access control
- `barryvdh/laravel-debugbar` - Development debugging
- `laravel/telescope` - Application monitoring (production debugging)

---

### Frontend Stack

**Blade Templating Engine**

**Why Blade:**
- Native to Laravel (zero configuration)
- Secure by default (auto-escapes output, prevents XSS)
- Component-based approach for reusability
- Simple syntax, easy to learn

**Best Practices:**
- Use `@component` and `@slot` for reusable UI elements
- Use `@include` for partials (header, footer, navigation)
- Layouts with `@extends` and `@section`
- **NEVER put business logic in Blade files** (use Controllers/Services)

**Tailwind CSS 4.0**

**Why Tailwind:**
- Utility-first CSS framework
- Highly customizable (brand colors, spacing, typography)
- Small production bundle (unused classes purged)
- Rapid development (no custom CSS needed)

**Configuration:**
Create `tailwind.config.js` with I-NNOVA CMR brand colors:

```javascript
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'innova-purple': '#7C3AED',
        'innova-blue': '#1E40AF',
        'innova-teal': '#14B8A6',
      },
    },
  },
}
```

**Vite (Modern Build Tool)**

**Why Vite:**
- Lightning-fast development server with Hot Module Replacement (HMR)
- Replaces Laravel Mix (deprecated)
- Modern ESbuild-based bundling
- Tree-shaking for optimized production builds

**JavaScript (ES6+)**

**Why Vanilla JS (Not React/Vue):**
- This platform doesn't need SPA complexity
- Server-side rendering (Blade) is faster for SEO
- Minimal JavaScript needed (charts, modals, AJAX)

**ES6+ Features to Use:**
- `async/await` for API calls
- `fetch` API for AJAX requests
- ES6 modules (`import/export`)
- Arrow functions
- Template literals
- Destructuring

**Optional JavaScript Libraries:**
- **Alpine.js** - Lightweight reactivity (optional, for dropdowns, modals)
- **Chart.js** - Career Capital progress graphs
- **Choices.js** - Enhanced select dropdowns with search

---

### Development Tools

**Required:**
- **Git** - Version control
- **Composer** - PHP dependency manager
- **NPM/Yarn** - JavaScript dependency manager
- **MySQL Server** - Database (localhost:3306)

**Recommended:**
- **VS Code** - IDE with Laravel extensions (Laravel Extra Intellisense, Laravel Blade Snippets)
- **Postman** - API testing
- **TablePlus** - Database GUI
- **Laravel Pint** - Code formatting (PSR-12)
- **PHPStan/Larastan** - Static analysis (level 8+)
- **PHP_CodeSniffer** - Code quality checks

---

## 5. SYSTEM ARCHITECTURE OVERVIEW

### Architecture Pattern

**MVC + Service Layer + Repository Pattern**

```
┌─────────────────────────────────────────────────────┐
│              PRESENTATION LAYER                     │
│      (Blade Views + Tailwind CSS + JavaScript)      │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│               CONTROLLER LAYER                      │
│   (HTTP request handling, validation, responses)    │
│   - FellowController                                │
│   - AdminCareerCapitalController                    │
│   - RecruiterController                             │
│   - InterviewController                             │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                SERVICE LAYER                        │
│         (Business logic orchestration)              │
│   - CareerCapitalService                            │
│   - InterviewService                                │
│   - RecruiterAccessService                          │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│              REPOSITORY LAYER                       │
│          (Database abstraction)                     │
│   - FellowRepository                                │
│   - TrackRepository                                 │
│   - ActivityRepository                              │
│   - InterviewSessionRepository                      │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                 MODEL LAYER                         │
│            (Eloquent ORM models)                    │
│   - User, Fellow, Track, FellowTrack                │
│   - Activity, InterviewSession, AuditLog            │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│             DATABASE (MySQL)                        │
└─────────────────────────────────────────────────────┘
```

**Why This Architecture:**

1. **Separation of Concerns** - Each layer has one responsibility
2. **Testability** - Service layer can be unit tested independently
3. **Maintainability** - Business logic isolated from controllers
4. **Scalability** - Easy to add new features without breaking existing code
5. **Team Collaboration** - Different developers can work on different layers

---

### Folder Structure

```
iks-career-capital/
├── app/
│   ├── Console/              # Artisan commands
│   ├── Enums/                # Enums (Tier, Role, ActivityType)
│   ├── Events/               # Events (CareerCapitalUpdated)
│   ├── Exceptions/           # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/        # Admin panel controllers
│   │   │   ├── Fellow/       # Fellow dashboard controllers
│   │   │   ├── Recruiter/    # Recruiter portal controllers
│   │   │   └── PublicProfile/ # Public Career Capital profiles
│   │   ├── Middleware/       # Custom middleware (RoleMiddleware)
│   │   └── Requests/         # Form validation classes
│   ├── Listeners/            # Event listeners
│   ├── Models/               # Eloquent models
│   ├── Repositories/         # Repository classes
│   ├── Services/             # Business logic services
│   └── ViewModels/           # ViewModels for complex Blade views
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories (testing)
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets (compiled CSS/JS)
├── resources/
│   ├── css/
│   │   └── app.css           # Tailwind CSS entry point
│   ├── js/
│   │   ├── app.js            # Main JavaScript
│   │   └── components/       # Reusable JS (charts, modals)
│   └── views/
│       ├── layouts/          # Master layouts
│       ├── components/       # Blade components
│       ├── fellow/           # Fellow dashboard views
│       ├── admin/            # Admin panel views
│       ├── recruiter/        # Recruiter portal views
│       ├── landing/          # Landing page
│       └── public-profile/   # Public profiles
├── routes/
│   ├── web.php               # Web routes
│   └── admin.php             # Admin-only routes (optional)
├── storage/                  # File storage, logs, cache
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── .env                      # Environment variables
├── composer.json             # PHP dependencies
├── package.json              # JavaScript dependencies
├── phpunit.xml               # PHPUnit configuration
└── vite.config.js            # Vite configuration
```

---

## 6. DATABASE SCHEMA REQUIREMENTS

### Migration Principles

**Every migration MUST:**
1. Be reversible (`up()` and `down()` methods)
2. Use descriptive names (e.g., `create_fellow_tracks_table`)
3. Include foreign key constraints with proper `onDelete` behavior
4. Add indexes on frequently queried columns
5. Use UUIDs for primary keys (security - prevents enumeration)

### Core Tables

#### Table: `users`

**Purpose:** Base user table for all system users

**Columns:**
- `id` (UUID, primary key)
- `email` (string, unique, indexed)
- `name` (string)
- `password` (string, hashed with bcrypt)
- `role` (enum: 'fellow', 'admin', 'mentor', 'recruiter')
- `location` (string, nullable) - City/Country
- `availability` (enum: 'immediate', '2_weeks', '1_month', '3_months', nullable)
- `email_verified_at` (timestamp, nullable)
- `remember_token` (string, nullable)
- `created_at`, `updated_at` (timestamps)
- `deleted_at` (timestamp, nullable - soft deletes)

**Indexes:**
- Primary: `id`
- Unique: `email`
- Index: `role`

**Notes:**
- Use UUIDs to prevent user enumeration attacks
- Soft deletes preserve data for compliance
- Role-based access control enforced via middleware

---

#### Table: `tracks`

**Purpose:** Career tracks (Full-Stack, Product, Design, etc.)

**Columns:**
- `id` (UUID, primary key)
- `name` (string, unique) - Example: "Full-Stack Engineering"
- `category` (enum: 'technical', 'non-technical', 'hybrid')
- `description` (text)
- `scoring_rubric` (JSON) - Example: `{"technical": 30, "interview": 25, "portfolio": 20, "collaboration": 15, "learning": 10}`
- `icon_url` (string, nullable) - Track icon for UI
- `is_active` (boolean, default true) - Admin can disable tracks
- `order` (integer, default 0) - Display order on frontend
- `created_at`, `updated_at` (timestamps)

**Indexes:**
- Primary: `id`
- Unique: `name`
- Index: `category`, `is_active`

**Notes:**
- Scoring rubric stored as JSON for flexibility
- Different tracks can have different weight distributions
- Admins can dynamically add/edit/disable tracks

---

#### Table: `fellow_tracks`

**Purpose:** Many-to-many relationship (Fellows × Tracks) with Career Capital score

**Columns:**
- `id` (UUID, primary key)
- `fellow_id` (UUID, foreign key → users.id)
- `track_id` (UUID, foreign key → tracks.id)
- `score` (decimal 5,2, default 0.00) - Range: 0.00 to 100.00
- `tier` (enum: 'rookie', 'intern', 'professional', 'elite', default 'rookie')
- `is_primary` (boolean, default false) - Only ONE primary track per fellow
- `effort_allocation` (integer, default 100) - Percentage (0-100)
- `started_at` (timestamp)
- `last_active` (timestamp, nullable)
- `created_at`, `updated_at` (timestamps)
- `deleted_at` (timestamp, nullable - soft deletes)

**Foreign Keys:**
- `fellow_id` → `users.id` (ON DELETE CASCADE)
- `track_id` → `tracks.id` (ON DELETE CASCADE)

**Indexes:**
- Primary: `id`
- Unique: `[fellow_id, track_id]` (prevent duplicates)
- Index: `score`, `tier`

**Business Rules (Enforced in Service Layer):**
- Only ONE `is_primary = true` per fellow
- Sum of `effort_allocation` across all active tracks MUST = 100%
- Tier auto-calculated based on score + admin settings

---

#### Table: `activities`

**Purpose:** All fellow activities (projects, interviews, blogs, mentoring, etc.)

**Columns:**
- `id` (UUID, primary key)
- `fellow_id` (UUID, foreign key → users.id)
- `track_id` (UUID, foreign key → tracks.id, nullable)
- `type` (enum: 'project', 'ai_interview', 'human_interview', 'blog_post', 'mentoring', 'hackathon', 'code_review', 'certification')
- `title` (string)
- `description` (text, nullable)
- `url` (string, nullable) - GitHub repo, blog link, demo URL
- `impact_metrics` (JSON, nullable) - Example: `{"users": 2400, "revenue": 15000000, "github_stars": 45}`
- `points_earned` (integer, default 0)
- `verified_by_admin_id` (UUID, foreign key → users.id, nullable)
- `admin_notes` (text, nullable) - Justification for points awarded
- `status` (enum: 'pending', 'approved', 'rejected', default 'pending')
- `submitted_at` (timestamp)
- `approved_at` (timestamp, nullable)
- `created_at`, `updated_at` (timestamps)
- `deleted_at` (timestamp, nullable - soft deletes)

**Foreign Keys:**
- `fellow_id` → `users.id` (ON DELETE CASCADE)
- `track_id` → `tracks.id` (ON DELETE SET NULL)
- `verified_by_admin_id` → `users.id` (ON DELETE SET NULL)

**Indexes:**
- Primary: `id`
- Index: `type`, `status`, `[fellow_id, track_id]`

**Notes:**
- Flexible `type` enum covers all activity categories
- `impact_metrics` as JSON allows different metrics per activity type
- Admin approval workflow prevents gaming the system
- `admin_notes` provides audit trail

---

#### Table: `interview_sessions`

**Purpose:** AI + Human mock interviews

**Columns:**
- `id` (UUID, primary key)
- `fellow_id` (UUID, foreign key → users.id)
- `track_id` (UUID, foreign key → tracks.id)
- `type` (enum: 'behavioral', 'technical_coding', 'system_design', 'product_case', 'design_challenge')
- `mode` (enum: 'ai', 'human')
- `interviewer_id` (UUID, foreign key → users.id, nullable) - NULL if AI
- `duration_minutes` (integer, nullable)
- `score` (decimal 5,2, nullable) - 0.00 to 100.00
- `transcript` (JSON, nullable) - Full conversation
- `feedback` (JSON, nullable) - AI analysis or human notes
- `rubric_scores` (JSON, nullable) - Example: `{"clarity": 92, "structure": 88, "confidence": 95}`
- `video_url` (string, nullable) - Recorded session (S3/Cloudinary)
- `created_at`, `updated_at` (timestamps)

**Foreign Keys:**
- `fellow_id` → `users.id` (ON DELETE CASCADE)
- `track_id` → `tracks.id` (ON DELETE CASCADE)
- `interviewer_id` → `users.id` (ON DELETE SET NULL)

**Indexes:**
- Primary: `id`
- Index: `[fellow_id, track_id]`, `type`, `mode`, `score`

**Notes:**
- Unified table for AI + human interviews (mode differentiates)
- `rubric_scores` as JSON allows flexible rubrics per interview type
- Video URL for recorded sessions (future feature)

---

#### Table: `audit_logs`

**Purpose:** Track all Career Capital score changes (compliance + transparency)

**Columns:**
- `id` (UUID, primary key)
- `fellow_id` (UUID, foreign key → users.id)
- `track_id` (UUID, foreign key → tracks.id)
- `admin_id` (UUID, foreign key → users.id) - Who made the change
- `previous_score` (decimal 5,2)
- `new_score` (decimal 5,2)
- `justification` (text) - Required, minimum 10 characters
- `related_activity_id` (UUID, nullable) - Activity that triggered change
- `created_at` (timestamp)

**Foreign Keys:**
- `fellow_id` → `users.id` (ON DELETE CASCADE)
- `track_id` → `tracks.id` (ON DELETE CASCADE)
- `admin_id` → `users.id` (ON DELETE CASCADE)

**Indexes:**
- Primary: `id`
- Index: `[fellow_id, track_id]`, `admin_id`

**Notes:**
- **IMMUTABLE** - Never update/delete audit logs
- Justification enforced in service layer (minimum 10 chars)
- Provides complete audit trail for compliance

---

#### Table: `recruiter_actions`

**Purpose:** Track recruiter interactions with fellows (for analytics)

**Columns:**
- `id` (UUID, primary key)
- `recruiter_id` (UUID, foreign key → users.id)
- `fellow_id` (UUID, foreign key → users.id)
- `action` (enum: 'viewed', 'saved', 'intro_requested', 'contacted', 'interviewed', 'hired')
- `notes` (text, nullable)
- `created_at`, `updated_at` (timestamps)

**Foreign Keys:**
- `recruiter_id` → `users.id` (ON DELETE CASCADE)
- `fellow_id` → `users.id` (ON DELETE CASCADE)

**Indexes:**
- Primary: `id`
- Index: `[recruiter_id, fellow_id]`, `action`

**Use Cases:**
- Track recruiter engagement (analytics dashboard)
- Prevent duplicate intro requests
- Show fellows who viewed their profile

---

#### Table: `subscriptions`

**Purpose:** Recruiter subscription management (Free, Partner, Premium tiers)

**Columns:**
- `id` (UUID, primary key)
- `recruiter_id` (UUID, foreign key → users.id)
- `tier` (enum: 'free', 'partner', 'premium', default 'free')
- `amount` (integer, default 0) - In XAF (Cameroon Francs)
- `billing_cycle` (enum: 'monthly', 'yearly', nullable)
- `status` (enum: 'active', 'cancelled', 'expired', 'trial', default 'trial')
- `stripe_subscription_id` (string, nullable) - Stripe reference
- `started_at` (timestamp)
- `expires_at` (timestamp, nullable)
- `created_at`, `updated_at` (timestamps)

**Foreign Keys:**
- `recruiter_id` → `users.id` (ON DELETE CASCADE)

**Indexes:**
- Primary: `id`
- Index: `recruiter_id`, `status`

**Notes:**
- Free tier: No payment, limited access (20 profiles/month)
- Partner/Premium: Stripe subscription required
- Expiration handled via Laravel Scheduler (daily job)

---

#### Table: `admin_settings`

**Purpose:** Dynamic system configuration (NO hardcoded values)

**Columns:**
- `id` (UUID, primary key)
- `key` (string, unique) - Example: "elite_tier_threshold"
- `value` (text) - Stored as JSON if complex, plain text if simple
- `type` (enum: 'integer', 'decimal', 'boolean', 'json', 'string')
- `description` (text) - Human-readable explanation for admins
- `category` (string, default 'general') - Group settings (career_capital, recruiter, billing)
- `created_at`, `updated_at` (timestamps)

**Indexes:**
- Primary: `id`
- Unique: `key`
- Index: `category`

**Example Rows:**

```
key: elite_tier_threshold
value: 61.00
type: decimal
description: Minimum score required for Elite tier
category: career_capital

key: interview_readiness_weight
value: 25
type: integer
description: Percentage weight of Interview Readiness in Career Capital score
category: career_capital

key: free_tier_profile_limit
value: 20
type: integer
description: Number of profiles free-tier recruiters can view per month
category: recruiter
```

**Notes:**
- Admins edit via Settings page in admin panel
- Service layer fetches settings dynamically
- Never hardcode thresholds/weights in code

---

## 7. USER ROLES & ACCESS CONTROL

### Roles Definition

**Four Primary Roles:**

1. **Fellow** - Career builders (main users)
2. **Admin** - Platform managers (I-NNOVA CMR team)
3. **Mentor** - Industry professionals (conduct human interviews)
4. **Recruiter** - Talent acquisition (hire fellows)

### Role Permissions (Using Spatie Laravel Permission Package)

**Install Package:**
```bash
composer require spatie/laravel-permission
```

**Permission Structure:**

**Fellow Permissions:**
- `view_fellow_dashboard` - Access own dashboard
- `edit_own_profile` - Update personal information
- `submit_activity` - Submit projects, blogs, etc.
- `take_interview` - Start AI/human interviews
- `switch_track` - Change primary track
- `view_peer_projects` - Browse project gallery

**Admin Permissions:**
- `manage_all_fellows` - View/edit any fellow profile
- `manage_tracks` - Create/edit/delete tracks
- `update_career_capital` - Manually adjust scores
- `approve_activities` - Approve/reject fellow submissions
- `manage_admin_settings` - Edit system configuration
- `view_analytics` - Access admin analytics
- `manage_recruiters` - Manage recruiter accounts

**Mentor Permissions:**
- `mentor_fellows` - Access to assigned fellows
- `conduct_human_interviews` - Schedule/conduct mock interviews
- `view_fellow_profiles` - See Career Capital details
- `provide_feedback` - Leave interview feedback

**Recruiter Permissions:**
- `view_fellow_profiles` - Browse talent marketplace
- `request_introductions` - Request warm intros to fellows
- `manage_subscription` - Update billing/plan
- `save_candidates` - Add to pipeline
- `download_profiles` - Export candidate data (PDF)

### Middleware Implementation

**Role-Based Route Protection:**

```php
// Example: Admin-only routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::resource('tracks', AdminTrackController::class);
    Route::post('/career-capital/update', [AdminCareerCapitalController::class, 'update']);
});

// Fellow-only routes
Route::middleware(['auth', 'role:fellow'])->prefix('fellow')->group(function () {
    Route::get('/dashboard', [FellowDashboardController::class, 'index']);
    Route::post('/activities', [FellowActivityController::class, 'store']);
});

// Recruiter-only routes
Route::middleware(['auth', 'role:recruiter'])->prefix('recruiter')->group(function () {
    Route::get('/talent', [RecruiterTalentController::class, 'index']);
    Route::post('/request-intro/{fellow}', [RecruiterIntroController::class, 'request']);
});
```

**Permission Checks in Controllers:**

```php
// Check permission before action
if (!auth()->user()->can('update_career_capital')) {
    abort(403, 'Unauthorized action.');
}
```

---

## 8. MULTI-TRACK CAREER CAPITAL SYSTEM

### System Overview

**Core Concept:**  
Fellows can pursue **multiple career tracks simultaneously**, each with independent Career Capital scores.

**Example:**
- **Primary Track (60% effort):** Full-Stack Engineering → 87% Elite
- **Secondary Track (30% effort):** Product Management → 52% Professional
- **Tertiary Track (10% effort):** DevOps → 28% Intern

### Track Management Rules

**Rule 1: One Primary Track**
- Fellow MUST have exactly ONE primary track
- Primary track = main career focus
- Displayed prominently on dashboard and recruiter profiles
- Enforced in service layer (not database constraint)

**Rule 2: Effort Allocation**
- Total effort across all active tracks MUST = 100%
- Example: 60% + 30% + 10% = 100% ✅
- Example: 50% + 50% + 20% = 120% ❌ (rejected)
- Validated before saving any track changes

**Rule 3: Track Switching**
- Fellows can switch primary track anytime
- Previous primary becomes secondary
- Requires 2-hour orientation for new track (future feature)
- Maximum 2 switches per quarter (prevent track-hopping)

**Rule 4: Track-Specific Scoring**
- Each track has custom scoring rubric
- Full-Stack: Technical (30%), Interview (25%), Portfolio (20%), Collaboration (15%), Learning (10%)
- Product: Product Craft (35%), Interview (25%), Execution (20%), Strategy (15%), Communication (5%)
- Admins can adjust weights per track

### Career Capital Calculation Logic

**Service Layer Method:**

```
calculateCareerCapital(fellowId, trackId):
    1. Fetch track scoring rubric (JSON from tracks table)
    2. For each category (technical, interview, portfolio, etc.):
        a. Query activities table for relevant activities
        b. Sum points earned in that category
        c. Calculate percentage score for category
    3. Apply weights from rubric
    4. Sum weighted scores = Overall Career Capital (0-100%)
    5. Determine tier based on admin settings
    6. Update fellow_tracks table
    7. Create audit log if score changed
```

**Example Calculation:**

```
Track: Full-Stack Engineering
Rubric: {technical: 30%, interview: 25%, portfolio: 20%, collaboration: 15%, learning: 10%}

Category Scores:
- Technical: 85% (12 projects, 520 GitHub commits, 1 hackathon win)
- Interview: 92% (47 mocks completed, 89% avg score)
- Portfolio: 78% (6 deployed apps, 3 case studies, website published)
- Collaboration: 88% (28 code reviews, 6 fellows mentored)
- Learning: 95% (8 courses, 3 certifications, 12 workshops)

Weighted Score:
(85 × 0.30) + (92 × 0.25) + (78 × 0.20) + (88 × 0.15) + (95 × 0.10)
= 25.5 + 23.0 + 15.6 + 13.2 + 9.5
= 86.8% (Elite Tier)
```

### Tier Determination

**Fetch Thresholds from Admin Settings:**

```
rookie_max = getSetting('rookie_tier_max', default: 20.00)
intern_max = getSetting('intern_tier_max', default: 40.00)
professional_max = getSetting('professional_tier_max', default: 60.00)
elite_min = professional_max + 0.01

If score <= rookie_max → Rookie
Else if score <= intern_max → Intern
Else if score <= professional_max → Professional
Else → Elite
```

**Tier Benefits (Frontend Display):**

- **Rookie (0-20%):**
  - Access to basic resources
  - Can submit activities
  - Can practice AI interviews

- **Intern (21-40%):**
  - All Rookie benefits +
  - 1-on-1 mentor office hours (monthly)
  - Resume review sessions
  - Featured on "Rising Stars" board

- **Professional (41-60%):**
  - All Intern benefits +
  - Priority job opportunities
  - Resume roast with hiring managers
  - Exclusive networking events
  - Can add secondary track

- **Elite (61-100%):**
  - All Professional benefits +
  - Direct recruiter introductions
  - Priority on marketplace (featured profiles)
  - Lifetime alumni status
  - Invited to annual Elite Summit
  - Can mentor new cohorts (paid)

---

## 9. INTERVIEW PREPARATION MODULE

### System Purpose

**Goal:** IKS fellows graduate with **40-60 practice interviews** completed, making them more prepared than 95% of candidates in the market.

**Why This Matters:**
- Most bootcamps teach skills but don't prepare for interviews
- Interview anxiety is the #1 reason candidates fail
- Recruiters trust fellows who've practiced extensively

### Interview Types & Points

**AI-Powered Interviews (Self-Service):**

1. **Behavioral Interviews (20-30 min)**
   - STAR-method questions ("Tell me about a time...")
   - AI evaluates: Structure, clarity, filler words, pacing
   - Points: 5 pts/session
   - Weekly limit: Max 20 pts (4 sessions/week)

2. **Technical Coding Interviews (45 min)**
   - Algorithm/data structure problems (LeetCode-style)
   - AI evaluates: Correctness, complexity, code quality
   - Points: 8 pts/session
   - Weekly limit: Max 24 pts (3 sessions/week)

3. **System Design Interviews (60 min)**
   - Architecture problems ("Design Instagram")
   - AI evaluates: Scalability, trade-offs, components
   - Points: 10 pts/session
   - Weekly limit: Max 20 pts (2 sessions/week)

4. **Track-Specific Interviews (30-45 min)**
   - Product: Case studies, metrics, strategy
   - Design: Redesign challenges, critiques
   - Points: 10 pts/session
   - Weekly limit: Max 20 pts (2 sessions/week)

**Human Mock Interviews (Scheduled):**

- 30-45 min with mentor/industry professional
- Randomized questions (no prep allowed)
- Scored on: Technical (30%), Communication (25%), Problem-Solving (25%), Culture Fit (20%)
- Points: 15 pts/session
- Weekly limit: Max 30 pts (2 sessions/week)
- Requirements: Must complete 10 AI mocks in same category first

**Strategy Workshops (Group Sessions):**

- 60-90 min workshops on interview tactics
- Topics: Resume storytelling, salary negotiation, handling failure questions
- Points: 5 pts/workshop
- Weekly limit: Max 10 pts (2 workshops/week)

### AI Interview Technology Stack

**Backend: OpenAI GPT-4 API**

**Features:**
- Generates interview questions dynamically
- Analyzes fellow responses
- Provides structured feedback
- Scores using predefined rubrics

**Frontend: Browser-Based**

- Text-based interface (Phase 1 - MVP)
- Voice interface (Phase 2 - using Web Speech API)
- Video recording (Phase 3 - using MediaRecorder API)

**Workflow:**

1. Fellow clicks "Start AI Interview"
2. System creates `interview_sessions` record (status: in_progress)
3. AI generates first question based on interview type
4. Fellow types/speaks response
5. AI analyzes response, generates next question
6. Repeat for 5-10 questions
7. AI generates final score + feedback
8. Update `interview_sessions` record with score, transcript, feedback
9. Award Career Capital points via `activities` table

### Interview Readiness Dashboard

**Fellow sees:**

- Overall Interview Readiness Score (0-100%)
- Breakdown by category:
  - Behavioral: 94% (18 AI + 4 human mocks)
  - Technical: 89% (32 coding challenges)
  - System Design: 86% (12 sessions)
  - Communication: 92% (Yoodli-style analysis)
- Progress graph (score over time)
- Next recommended practice (e.g., "Focus on System Design - currently 86%, aim for 90%+")
- Schedule human mock button

**Recruiter sees (on fellow profile):**

- Interview Readiness Score: 89/100
- Total interview count: 54 (46 AI + 8 human)
- Category breakdown with percentiles
- Sample interview video (2-min highlight reel - future feature)
- Downloadable Interview Performance Report (PDF)

---

## 10. ADMIN PANEL - DYNAMIC & CUSTOMIZABLE

### Core Philosophy

**EVERYTHING MUST BE ADMIN-CONFIGURABLE**

**Zero hardcoded values.** Every system parameter editable via admin panel.

### Admin Dashboard Sections

**1. Fellows Management**
- View all fellows (table with search, filters)
- View individual fellow profile (Career Capital breakdown, activities, interviews)
- Manually adjust Career Capital scores (with justification)
- Approve/reject activities
- Send notifications to fellows
- View at-risk fellows (score stagnant >2 weeks)

**2. Tracks Management**
- Create new tracks (name, description, icon, category)
- Edit track scoring rubrics (adjust weight percentages)
- Activate/deactivate tracks
- Reorder tracks (display order on frontend)
- View track analytics (how many fellows per track, avg scores)

**3. Activities Review**
- Pending activities queue (approve/reject with notes)
- Batch actions (approve multiple at once)
- Spotlight feature (feature exceptional projects on homepage)
- Activity statistics (total submitted, approved rate, rejection reasons)

**4. Interview Management**
- View all interview sessions (filter by fellow, type, score)
- Schedule human mock interviews
- Assign mentors to conduct interviews
- View interview analytics (avg scores, completion rates)

**5. Recruiter Management**
- View all recruiter accounts
- Manage subscriptions (upgrade/downgrade manually)
- View recruiter activity (profiles viewed, intros requested)
- Approve/reject intro requests
- Send messages to recruiters

**6. System Settings**
- **Career Capital Settings:**
  - Tier thresholds (Rookie: 0-20%, Intern: 21-40%, etc.)
  - Category weights per track
  - Interview points per session type
- **Recruiter Settings:**
  - Free tier limits (20 profiles/month)
  - Subscription pricing (Partner: XAF 300K, Premium: XAF 1.2M)
  - Intro request approval workflow (auto-approve or manual)
- **General Settings:**
  - Platform name, logo, tagline
  - Contact email, phone
  - Social media links
  - Maintenance mode toggle

**7. Analytics Dashboard**
- Total fellows (by tier, by track)
- Career Capital avg per track
- Interview completion rates
- Recruiter engagement metrics
- Revenue (subscriptions, projections)
- Platform health (uptime, performance)

### Admin Settings Table Structure

**Key-Value Storage (Flexible, Future-Proof):**

```
admin_settings table:
- key: elite_tier_threshold
- value: 61.00
- type: decimal
- description: Minimum score for Elite tier
- category: career_capital

Admin edits via UI:
[Elite Tier Threshold]
[Description: Minimum score for Elite tier]
[Value: 61.00] [Type: Decimal]
[Update Button]
```

**Service Layer Fetches Settings:**

```php
$eliteThreshold = AdminSetting::where('key', 'elite_tier_threshold')->value('value');
// Or use repository pattern:
$eliteThreshold = app(AdminSettingsRepository::class)->get('elite_tier_threshold', default: 61.00);
```

**Benefits:**
- No code changes needed to adjust system behavior
- Admins control everything via UI
- Settings versioned (can track changes)
- Export/import settings (backup/restore)

---

## 11. FELLOW DASHBOARD & EXPERIENCE

### Dashboard Layout

**Header Section:**
- Fellow name
- Primary track + tier + score (e.g., "Full-Stack Engineering - Elite (87%)")
- Time in program (e.g., "16 weeks active")
- Quick actions: [View Portfolio] [Go to Bibliotech]

**Main Content (3-Column Layout):**

**Left Sidebar (Career Capital Breakdown):**
- Circular progress chart (overall score)
- Category breakdown (5 bars with percentages)
- Multi-track management:
  - List all active tracks
  - Set primary track button
  - Add new track button

**Center Column (Activity Feed):**
- Weekly progress (4 pillars: Build, Brand, Interview, Collaborate)
  - Visual checkboxes (✅ or ⏳)
  - Warning if not all complete: "⚠️ Complete all 4 to prevent score freeze"
- Recent activities (last 10):
  - Icon + title + description
  - Points earned
  - Status badge (approved/pending/rejected)
- Submit new activity button

**Right Sidebar (Next Actions & Recommendations):**
- Interview readiness stats:
  - Total interviews completed
  - Avg score
  - Category breakdown (Behavioral: 94%, Technical: 89%, etc.)
- Next recommended actions:
  - "Complete 5 more technical mocks to reach 90%"
  - "Enroll in System Design course on Bibliotech"
  - "Schedule your first human mock interview"
- Peer project gallery (5 latest projects from other fellows)

### Weekly Accountability System (4 Pillars)

**Purpose:** Prevent fellows from going inactive, ensure consistent progress

**Rule:** Fellows must complete all 4 pillars each week or score freezes

**Pillar 1: BUILD (25%)**
- Submit 1 project demo (2-min video or live link)
- GitHub push (code committed)
- Points: 25 pts

**Pillar 2: BRAND (15%)**
- Publish 1 LinkedIn post OR blog post OR Twitter thread
- About project, learning, or industry topic
- Points: 15 pts

**Pillar 3: INTERVIEW (20%)**
- Complete 1 AI mock interview OR attend 1 workshop
- Points: 5-10 pts (depending on interview type)

**Pillar 4: COLLABORATE (15%)**
- Give 2 peer code reviews OR 1 mentoring session
- Points: 10-15 pts

**Enforcement:**
- If any pillar incomplete by Sunday 11:59 PM, score frozen
- Fellow notified via email/SMS: "Complete missing pillars to unfreeze score"
- Score unfreezes when all 4 completed

### Portfolio Builder

**Features:**
- Drag-and-drop project cards
- Auto-generates public portfolio URL:
  - Format: `{fellow-name}.iks.innovacm.co`
  - Example: `akeem-tanyi.iks.innovacm.co`
- Sections:
  - About (bio, skills, location, availability)
  - Projects (with case studies)
  - Certifications
  - Resume (PDF upload + inline display)
  - Contact form (recruiters can send message)
- SEO-optimized (meta tags, Open Graph, schema.org)
- Export to PDF (for offline sharing)

### Track Switching Workflow

**Step 1: Fellow requests switch**
- Clicks "Switch Primary Track" button
- Selects new track from dropdown
- Writes justification (150 words): "Why are you switching?"

**Step 2: Admin reviews**
- Notification sent to admin
- Admin sees:
  - Fellow name
  - Current track + score
  - Requested track
  - Justification
- Admin approves/rejects

**Step 3: Switch executed**
- Previous primary track becomes secondary
- New track becomes primary
- Effort allocation adjusted (admin can edit)
- Fellow receives confirmation email

**Limits:**
- Max 2 switches per quarter
- Must be Intern tier (21%+) in current track to switch

---

## 12. RECRUITER TALENT MARKETPLACE

### Marketplace Purpose

**For Recruiters:**  
Access pre-vetted, interview-ready talent with transparent skill metrics

**For Fellows:**  
Get discovered by companies without cold applying

**For I-NNOVA CMR:**  
Revenue through recruiter subscriptions

### Talent Discovery Features

**Search & Filters:**
- **By Tier:** Elite, Professional, Intern
- **By Track:** Full-Stack, Product, Design, AI/ML, etc.
- **By Score Range:** 70-100%, 50-69%, etc.
- **By Location:** Yaoundé, Douala, Bamenda, Remote
- **By Availability:** Immediate, 2 weeks, 1 month
- **By Skills:** React, Python, AWS, Figma (auto-extracted from projects)
- **By Interview Readiness:** 90%+ overall, 95%+ behavioral, etc.
- **By Project Impact:** 100+ users, revenue generated, hackathon wins

**Sort Options:**
- Career Capital Score (high → low)
- Interview Readiness Score
- Recently Active
- Most Projects Shipped

**View Modes:**
- Grid view (profile cards)
- List view (table with key stats)

### Candidate Profile Page (Recruiter View)

**Header:**
- Fellow name
- Primary track + tier + score
- Location, availability, salary expectation

**Career Capital Overview:**
- Overall profile score (weighted: Primary 70% + Secondary 20% + Tertiary 10%)
- Score breakdown per track (if multi-track)
- Tier badges

**Interview Readiness Section:**
- Overall score (87/100)
- Verified practice volume: 54 interviews (46 AI + 8 human)
- Category breakdown:
  - Behavioral: 94/100
  - Technical: 89/100
  - System Design: 86/100
  - Communication: 92/100
- Sample interview video (2-min highlight - future)
- Downloadable performance report (PDF)

**Portfolio Highlights:**
- Top 3-5 projects with metrics
- Live demo links
- Case studies (problem → solution → impact)
- GitHub repos (with stars/forks)

**Verified Achievements:**
- Hackathon wins
- Freelance revenue generated
- Certifications earned
- Community contributions

**Recruiter Actions:**
- [Request Introduction] - Admin facilitates warm intro
- [Save to Pipeline] - Add to custom folder (e.g., "Fintech Hires Q1")
- [Schedule Call] - Direct contact (if fellow opted in)
- [Download Profile PDF] - Export full profile

### Subscription Tiers

**FREE TIER (Lead Generation):**
- Cost: XAF 0/month
- Access:
  - Browse 20 profiles/month
  - See basic scores (tier only, not exact %)
  - View public portfolios
  - Download 5 resumes/month
  - Monthly newsletter with top graduates

**PARTNER TIER (Most Popular):**
- Cost: XAF 300,000/year (~$500 USD)
- Access:
  - Unlimited profile views
  - Advanced filters (all features)
  - Request warm intros (I-NNOVA admin facilitates)
  - Quarterly demo days (meet 10 Elite fellows in-person)
  - Early access to new Elite grads (48-hour head start)
  - Download unlimited resumes
  - Save unlimited to pipeline
  - Analytics dashboard (hiring funnel)
  - 1 free job posting on IKS careers board

**PREMIUM TIER (Enterprise):**
- Cost: XAF 1,200,000/year (~$2,000 USD)
- Access:
  - Everything in Partner +
  - Custom talent assessments (upload your coding test)
  - Headhunter mode (I-NNOVA recommends top 5 candidates)
  - White-label reports (branded PDFs for your team)
  - API access (integrate into your ATS)
  - Exclusive talent pool (first access before public)
  - Co-branded hackathons (sponsor + hire winners)

### Access Control (Subscription Limits)

**Free Tier Enforcement:**

```
Monthly Profile View Limit: 20

Implementation:
- Store view count in cache: recruiter_views_{recruiter_id}_{current_month}
- Increment on each profile view
- Check before allowing view:
  if (viewsThisMonth >= 20 && subscription.tier === 'free') {
      throw new Exception('Monthly limit reached. Upgrade to Partner tier for unlimited access.');
  }
- Reset counter on 1st of each month
```

**Partner/Premium Tiers:**
- No view limits
- No download limits

**Intro Request Limits:**

```
Free: 0 intro requests/month (must upgrade)
Partner: 5 intro requests/month
Premium: Unlimited intro requests
```

### Recruiter Analytics Dashboard

**Metrics Displayed:**

**Hiring Funnel (Last 90 Days):**
- Profiles viewed: 47
- Candidates saved: 18 (38% conversion)
- Intro requests: 12 (67% conversion)
- Interviews conducted: 8 (67% conversion)
- Hires made: 2 (25% conversion)

**Benchmark Comparison:**
- Industry avg interview→hire rate: 15%
- Your rate: 25% (67% better!)

**Hires from IKS:**
- List each hire:
  - Name, role, salary, hire date
  - Performance review (90-day)
  - Time-to-hire (vs company avg)

**Cost Savings:**
- Traditional recruiter fee (20%): XAF 6M
- IKS subscription (3 months): XAF 75K
- Net savings: XAF 5.925M (7,900% ROI)

---

## 13. PUBLIC CAREER CAPITAL PROFILES

### Purpose

**SEO-Friendly Public Profiles** for each fellow's track

**URL Structure:**
```
Format: /profile/{fellow-username}/{track-slug}
Example: /profile/akeem-tanyi/full-stack-engineering
```

### Features

**SEO Optimization:**
- Unique title tag: "Akeem Tanyi - Full-Stack Engineering - IKS Career Capital"
- Meta description: "Akeem is an Elite Full-Stack Engineer with 87% Career Capital. View verified projects, interview readiness, and portfolio."
- Open Graph tags (for social media sharing)
- Schema.org structured data (Person type)
- Canonical URL
- Sitemap inclusion

**Profile Content:**

**Header:**
- Fellow name
- Track name
- Tier + Score (87% Elite)
- Stats cards:
  - Career Capital: 87%
  - Tier: Elite
  - Interviews: 54 completed

**Career Capital Breakdown:**
- Visual bars for each category
- Percentages shown

**Projects Showcase:**
- Grid layout (2 columns)
- Project cards with:
  - Title
  - Description (2-3 sentences)
  - Tech stack
  - Link to live demo/GitHub

**Interview Readiness:**
- Overall score
- Category breakdown
- Total interview count

**Footer CTA:**
- "This is a verified Career Capital profile from IKS"
- [Contact This Candidate] button → Recruiter contact form
- Powered by I-NNOVA CMR branding

**Access Control:**
- Public (no login required)
- Fellow can toggle visibility on/off
- Fellow can hide specific projects
- Analytics: Track profile views (for fellow)

---

## 14. LANDING PAGE DESIGN

### Purpose

**Premium, trust-driven landing page** that converts visitors into fellows or recruiters

### Structure (15 Sections)

**1. Hero Section:**
- Headline: "Build Verifiable Career Capital. Land Your Dream Role."
- Subheadline: "The first multi-track career development platform that proves you're ready—not just teaches you."
- CTAs: [Start Building →] [For Recruiters]
- Trust signals: "247 fellows · 42 Elite graduates · Powered by I-NNOVA CMR"
- Background: Animated gradient (I-NNOVA purple → blue → teal)

**2. Trust Bar:**
- Partner company logos (Flutterwave, Paystack, Andela, etc.)
- "42 companies hired IKS fellows in 2025"

**3. Problem Statement:**
- Headline: "You've Built Projects. Studied Algorithms. Updated Your Resume. Again. But Recruiters Still Ghost You. Why?"
- 2-column comparison: Old Way vs IKS Way

**4. How It Works (4-Step Visual):**
- Step 1: Choose Your Track(s)
- Step 2: Build & Verify
- Step 3: Prove Your Readiness (Interview Prep)
- Step 4: Get Hired (Recruiter Marketplace)

**5. Career Capital Explainer:**
- Interactive chart (hover to explore categories)
- "What Is Career Capital?" definition
- "Every point is earned. Every metric is verified."

**6. Multi-Track Showcase:**
- "Your Career Isn't Linear. Neither Is IKS."
- Animated diagram: Fellow starts Full-Stack, adds Product, switches primary
- Video testimonial: Fellow's track-switching journey

**7. Interview Prep (Flagship Feature):**
- Split screen: Traditional candidate vs IKS fellow
- "47 practice interviews vs 0-2"
- Interactive demo: "Try AI mock interview"

**8. For Recruiters:**
- 3-column benefits: Faster Hiring, Better Quality, Lower Cost
- Video testimonial: CTO from partner company
- CTAs: [Explore Talent Pool →] [Book Demo →]

**9. Social Proof:**
- Scrolling testimonial cards (auto-play)
- Stats ticker: "42 fellows hired, XAF 450M salaries earned, 89% interview pass rate"

**10. Live Activity Feed:**
- Real-time WebSocket stream: "Akeem just reached Elite! Blessing shipped 240-user project!"

**11. I-NNOVA CMR Ecosystem:**
- Product showcase (Marketplace, POS, BookIt, Bibliotech, etc.)
- Message: "Part of something bigger—building Cameroon's Silicon Valley"

**12. Pricing:**
- For Fellows: 100% FREE FOREVER
- For Recruiters: Free / Partner (XAF 300K/year) / Premium (custom)

**13. FAQ:**
- Interactive accordion (10-15 questions)
- Is this a bootcamp? How long to Elite? Can I switch tracks? vs LinkedIn?

**14. Final CTA:**
- "Join the I-NNOVA CMR Movement"
- "247 fellows building. 42 Elite graduates. Cameroon's Silicon Valley starts here."
- Two-path CTA: [I'm a Fellow] [I'm a Recruiter]

**15. Footer:**
- I-NNOVA CMR branding
- Links: Fellows, Recruiters, Products, Blog, Careers
- Social media
- Location: "Headquarters: City Chemist, Bamenda, Cameroon"
- "Transforming communities, empowering innovators."

### Design Principles

**Colors:**
- Primary: I-NNOVA Purple (#7C3AED)
- Secondary: I-NNOVA Blue (#1E40AF)
- Accent: I-NNOVA Teal (#14B8A6)
- Dark Mode: Default (bg: #0F172A, card: #1E293B, text: #F1F5F9)

**Typography:**
- Headings: Inter Bold (H1: 72px, H2: 48px, H3: 36px)
- Body: Inter Regular (18px)
- Code: JetBrains Mono

**Animations:**
- Scroll-triggered fade-in + slide-up
- Number counters (0 → final value)
- Progress bars fill on scroll
- Hover: Lift 4px + glow border

**Responsive:**
- Desktop: 1440px+
- Laptop: 1024px
- Tablet: 768px
- Mobile: 375px

---

## 15. ENGINEERING BEST PRACTICES

### Code Organization

**MVC Separation:**
- **Models:** Only database relationships + accessors/mutators
- **Controllers:** Only HTTP handling + validation + response
- **Services:** ALL business logic (Career Capital calculation, interview scoring, etc.)
- **Repositories:** Database queries (abstract SQL away from services)

**Example:**

```php
// BAD (Business logic in controller):
public function update(Request $request) {
    $fellow = User::find($request->fellow_id);
    $fellow->score = $request->new_score;
    $fellow->tier = ($request->new_score >= 61) ? 'elite' : 'professional';
    $fellow->save();
}

// GOOD (Business logic in service):
public function update(UpdateCareerCapitalRequest $request) {
    $this->careerCapitalService->updateCareerCapital(
        fellowId: $request->fellow_id,
        trackId: $request->track_id,
        newScore: $request->new_score,
        adminId: auth()->id(),
        justification: $request->justification
    );
}
```

### SOLID Principles

**S - Single Responsibility:**
- Each class does ONE thing
- CareerCapitalService = Career Capital logic ONLY
- InterviewService = Interview logic ONLY

**O - Open/Closed:**
- Open for extension, closed for modification
- Use interfaces for flexibility
- Strategy pattern for different interview types

**L - Liskov Substitution:**
- Subclasses must be substitutable for parent classes
- Use abstract classes wisely

**I - Interface Segregation:**
- Many small interfaces > one large interface
- `Scoreable`, `Verifiable`, `Reportable`

**D - Dependency Inversion:**
- Depend on abstractions, not concretions
- Use dependency injection (Laravel Service Container)

### Security Best Practices

**Input Validation:**
- ALWAYS use Form Requests (never trust `$request->input()` directly)
- Whitelist allowed values (enum validation)
- Type-cast inputs (integer, float, boolean)

**SQL Injection Prevention:**
- ALWAYS use Eloquent ORM (parameterized queries)
- NEVER use raw SQL with user input (unless absolutely necessary + parameterized)

**XSS Prevention:**
- Blade auto-escapes output: `{{ $variable }}`
- If raw HTML needed, sanitize: `{!! clean($html) !!}`

**CSRF Protection:**
- Laravel automatically includes CSRF tokens in forms
- Always use `@csrf` directive in Blade forms

**Authentication:**
- Use Laravel Sanctum for API tokens
- bcrypt for password hashing (built-in)
- Rate limiting on login attempts (Laravel throttle middleware)

**Authorization:**
- Use Spatie Permission package for role-based access
- Check permissions in controllers: `auth()->user()->can('permission')`

**Data Encryption:**
- Sensitive data encrypted at rest: `encrypt($data)` / `decrypt($encryptedData)`
- HTTPS enforced in production

### Performance Optimization

**Database Queries:**
- ALWAYS use eager loading to prevent N+1 queries:
  ```php
  // BAD:
  $fellows = Fellow::all();
  foreach ($fellows as $fellow) {
      echo $fellow->track->name; // N+1 query
  }

  // GOOD:
  $fellows = Fellow::with('track')->get();
  foreach ($fellows as $fellow) {
      echo $fellow->track->name; // No extra queries
  }
  ```

**Indexes:**
- Add indexes on foreign keys
- Add indexes on frequently queried columns (`score`, `tier`, `status`)
- Composite indexes for multi-column queries

**Caching:**
- Cache admin settings (rarely change):
  ```php
  $eliteThreshold = Cache::remember('elite_tier_threshold', 3600, function() {
      return AdminSetting::where('key', 'elite_tier_threshold')->value('value');
  });
  ```
- Cache recruiter profile view counts
- Cache Career Capital breakdowns (invalidate on score update)

**Job Queues:**
- AI interview processing (background job)
- Email sending (background job)
- Heavy calculations (background job)

**Pagination:**
- ALWAYS paginate large datasets (20-50 items/page)
- Use `simplePaginate()` when page numbers not needed (faster)

---

## 16. SECURITY & DATA PROTECTION

### Data Privacy

**Sensitive Data:**
- Passwords: bcrypt hashed (never store plain text)
- API keys: stored in `.env`, never in version control
- Personal info: encrypted if required (phone numbers, addresses)

**GDPR/Privacy Compliance:**
- Data export: Fellows can download all their data (JSON/PDF)
- Data deletion: Fellows can request account deletion (soft delete)
- Consent: Terms of Service + Privacy Policy acceptance on signup

### Access Control

**Role-Based Access:**
- Fellows: See only their own data
- Admins: See all data
- Recruiters: See only public/approved data

**Row-Level Security:**
- Query scoping:
  ```php
  // Ensure fellow sees only their activities
  Activity::where('fellow_id', auth()->id())->get();

  // Global scope for soft deletes
  Activity::withoutGlobalScope(SoftDeletingScope::class)->get();
  ```

### Audit Logging

**All sensitive actions logged:**
- Career Capital score changes
- Track switches
- Activity approvals/rejections
- Recruiter intro requests
- Admin setting changes

**Audit Log Table:**
- Immutable (never update/delete)
- Stores: Who, what, when, why
- Retention: Permanent (compliance)

---

## 17. QUALITY ASSURANCE REQUIREMENTS

### Testing Strategy

**Unit Tests (80%+ Coverage):**
- Test all service layer methods
- Test repositories
- Test models (relationships, accessors, mutators)

**Feature Tests:**
- Test complete user workflows:
  - Fellow submits activity → Admin approves → Score updates
  - Recruiter views profile → Requests intro → Admin approves
  - Fellow switches track → Score recalculated

**Integration Tests:**
- Test external API integrations (when added):
  - GitHub API
  - OpenAI API
  - Stripe API

**Browser Tests (Optional - Laravel Dusk):**
- Test JavaScript-heavy features
- Test cross-browser compatibility

### Test Examples

```php
// Unit Test: CareerCapitalService
public function test_calculate_tier_returns_elite_for_score_above_threshold()
{
    $service = new CareerCapitalService();
    $tier = $service->calculateTier(87.5);
    $this->assertEquals('elite', $tier);
}

// Feature Test: Activity Submission
public function test_fellow_can_submit_activity()
{
    $fellow = User::factory()->create(['role' => 'fellow']);
    $track = Track::factory()->create();

    $response = $this->actingAs($fellow)->post('/fellow/activities', [
        'track_id' => $track->id,
        'type' => 'project',
        'title' => 'E-commerce Platform',
        'description' => 'Built with Laravel + React',
        'url' => 'https://github.com/akeem/ecommerce',
    ]);

    $response->assertStatus(302); // Redirect
    $this->assertDatabaseHas('activities', [
        'fellow_id' => $fellow->id,
        'title' => 'E-commerce Platform',
        'status' => 'pending',
    ]);
}
```

### Code Quality Checks

**Laravel Pint (PSR-12 Formatting):**
```bash
./vendor/bin/pint
```

**PHPStan (Static Analysis - Level 8):**
```bash
./vendor/bin/phpstan analyse
```

**PHP_CodeSniffer:**
```bash
./vendor/bin/phpcs --standard=PSR12 app/
```

---

## 18. PERFORMANCE & OPTIMIZATION

### Database Optimization

**Indexes:**
```sql
-- Foreign keys
INDEX idx_fellow_id ON activities(fellow_id)
INDEX idx_track_id ON activities(track_id)

-- Frequently queried columns
INDEX idx_score ON fellow_tracks(score)
INDEX idx_tier ON fellow_tracks(tier)
INDEX idx_status ON activities(status)

-- Composite indexes
INDEX idx_fellow_track ON fellow_tracks(fellow_id, track_id)
```

**Query Optimization:**
- Use `select()` to fetch only needed columns
- Use `limit()` for large datasets
- Avoid `SELECT *` (fetch specific columns)

**Database Connection Pooling:**
- Configure in `config/database.php`
- Max connections: 100 (adjust based on traffic)

### Caching Strategy

**Cache Drivers:**
- Development: `file` or `array`
- Production: `redis` (fast, scalable)

**What to Cache:**
- Admin settings (1 hour TTL)
- Track lists (1 day TTL)
- Career Capital breakdowns (10 min TTL, invalidate on update)
- Public profiles (1 hour TTL)

**Cache Tags (Redis):**
```php
Cache::tags(['fellow', 'career-capital'])->put('fellow_87_cc', $data, 600);
Cache::tags(['fellow'])->flush(); // Clear all fellow-related cache
```

### Frontend Optimization

**Asset Optimization (Vite):**
- Minification (automatic in production)
- Tree-shaking (removes unused JS/CSS)
- Code splitting (lazy load routes)

**Image Optimization:**
- Use WebP format (90% smaller than PNG)
- Lazy loading: `<img loading="lazy">`
- CDN for static assets (Cloudinary)

**JavaScript:**
- Minimize third-party libraries
- Defer non-critical JS: `<script defer>`
- Use Alpine.js (12KB) instead of Vue/React (100KB+)

---

## 19. DEVELOPMENT WORKFLOW

### Git Workflow

**Branching Strategy:**
- `main` - Production-ready code (never commit directly)
- `develop` - Integration branch
- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/critical-issue` - Emergency fixes

**Commit Messages (Conventional Commits):**
```
feat: add Career Capital score calculation service
fix: resolve N+1 query in fellow dashboard
docs: update README with setup instructions
test: add unit tests for InterviewService
refactor: extract validation logic into FormRequest
```

**Pull Request Process:**
1. Create feature branch: `git checkout -b feature/multi-track-system`
2. Implement feature, write tests
3. Run tests: `php artisan test`
4. Run code quality checks: `./vendor/bin/pint && ./vendor/bin/phpstan analyse`
5. Commit with meaningful message
6. Push to GitHub: `git push origin feature/multi-track-system`
7. Create Pull Request (PR) with description
8. Code review by senior engineer
9. Merge to `develop` after approval
10. Deploy to staging for QA testing
11. Merge `develop` → `main` for production deployment

### Environment Setup

**Local Development:**
```bash
# 1. Clone repository
git clone https://github.com/innova-cmr/iks-career-capital.git
cd iks-career-capital

# 2. Install dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate
php artisan db:seed

# 5. Build assets
npm run dev

# 6. Start server
php artisan serve
```

**Environment Variables (.env):**
```
APP_NAME="IKS Career Capital"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iks_career_capital
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

# OpenAI API (for interviews)
OPENAI_API_KEY=sk-...

# Stripe (for recruiter subscriptions)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

---

## 20. CRITICAL SUCCESS METRICS

### Platform Health Metrics

**Fellows:**
- Total enrolled: 500 (Year 1 target)
- Weekly active users: 40%+
- Tier distribution:
  - Rookie: 35%
  - Intern: 35%
  - Professional: 20%
  - Elite: 10%
- Average Career Capital score: 45%
- Churn rate: <5%

**Interview Prep:**
- Avg interviews per fellow: 25+
- Interview completion rate: 80%+
- Avg score: 75%+

**Recruiters:**
- Total subscribed: 50 (Year 1)
- Free tier: 30
- Partner tier: 15
- Premium tier: 5
- Monthly active recruiters: 70%+

**Placements:**
- Fellows hired: 100+ (Year 1)
- Avg time-to-hire: 12 days (vs industry 45 days)
- Avg salary: XAF 10M/year
- Fellow satisfaction: 4.5+ stars

**Revenue:**
- Year 1: XAF 24M (~$40K USD)
- Year 3: XAF 105M (~$175K USD)

### Technical Metrics

**Performance:**
- Page load time: <2 seconds
- Database query time: <100ms (avg)
- Uptime: 99.9%+
- API response time: <200ms

**Code Quality:**
- Test coverage: 80%+
- PHPStan level: 8
- Zero critical security vulnerabilities
- Code review approval rate: 95%+

---

## FINAL NOTES FOR ENGINEERS

### This Is a Flagship System

**Treat it like enterprise software:**
- Write code you'd be proud to show in 5 years
- Document everything (future developers will thank you)
- Test rigorously (bugs cost reputation)
- Optimize early (technical debt compounds)

### Quality Over Speed

**I-NNOVA CMR spent 3+ years building products before launch.**

You have permission to:
- Refactor messy code
- Write comprehensive tests
- Request code reviews
- Push back on unrealistic deadlines

**Never compromise on:**
- Security
- Data integrity
- User experience
- Code maintainability

### Ask Questions

**If anything in this spec is unclear:**
- Ask the product team
- Discuss with fellow engineers
- Document your decisions (in code comments + README)

### Build for Africa

**This platform serves African talent.**

Consider:
- Slow internet connections (optimize assets)
- Mobile-first design (70% of users on mobile)
- Data costs (minimize API calls, cache aggressively)
- Local payment methods (M-Pesa, Mobile Money - future)

---

## DOCUMENT END

**Version:** 1.0  
**Last Updated:** February 2026  
**Maintained by:** I-NNOVA CMR Engineering Team  
**Contact:** engineering@innovacmr.com

---

**This specification is a living document. Update it as the product evolves.**

**Now go build something amazing.** 🚀🇨🇲
