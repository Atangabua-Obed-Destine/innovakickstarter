# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 8: Docker & Containerization Fundamentals

**Status:** Draft for review
**Unlocks:** After Milestone 7 is fully completed
**Theme:** A pivotal Milestone — images, containers, Dockerfiles, and registries. Everything from Terraform (Milestone 9) through Kubernetes (Milestone 11) assumes fellows are comfortable containerizing an application. This shifts the track from "infrastructure" toward "modern deployment practices."
**Target fellow:** AWS-infrastructure-fluent (compute, storage, networking); has never used Docker or any container technology.

---

### Activity 8.1 — Why Containers? The "Works on My Machine" Problem
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 7.7
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow researches the classic "works on my machine" problem (an app runs fine for the developer but breaks in production due to environment differences — OS version, missing dependency, different config) and explains how containers solve this by packaging an application with everything it needs to run, consistently, anywhere. Fellow relates this to their own Milestone 4 EC2 user-data script experience — was that approach actually reproducible, or fragile?
- **Rubric:**
  1. *Understanding* (60%) — Is the "works on my machine" problem and container solution correctly explained?
  2. *Applied Reflection* (40%) — Is the connection to their own prior bootstrap-script experience genuine and insightful?

---

### Activity 8.2 — Docker Fundamentals: Images & Containers
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 8.1 | **Prerequisites:** Activity 8.1
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Install Docker (on their EC2 instance or local machine) and learn the core distinction: an image is a blueprint, a container is a running instance of that image. Fellow pulls and runs a few official images (e.g., `nginx`, `hello-world`), explores `docker ps`, `docker images`, `docker stop`/`docker rm`, and screenshots a running container along with its logs (`docker logs`).
- **Rubric:**
  1. *Correct Command Usage* (60%) — Are Docker commands correctly used?
  2. *Conceptual Understanding* (40%) — Is the image-vs-container distinction correctly demonstrated?

---

### Activity 8.3 — Writing Your First Dockerfile
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.2 | **Prerequisites:** Activity 8.2
- **Evidence Required:** GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Learn Dockerfile instructions (`FROM`, `COPY`, `RUN`, `WORKDIR`, `EXPOSE`, `CMD`). Fellow writes a Dockerfile that containerizes a simple web app of their choice (a static HTML page via nginx, or a simple app from a prior track if available — e.g., the Software Engineering track's Express API), builds the image (`docker build`), runs it, and verifies it works correctly, pushing the Dockerfile and app code to a GitHub repo.
- **Rubric:**
  1. *Correct Dockerfile* (60%) — Does the Dockerfile correctly build and produce a working container?
  2. *Best Practices* (40%) — Are basic best practices followed (appropriate base image, minimal layers, no unnecessary bloat)?

---

### Activity 8.4 — Environment Variables & Configuration
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.3 | **Prerequisites:** Activity 8.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn why hardcoding configuration (database URLs, API keys, ports) into a Docker image is bad practice, and how to use environment variables (`docker run -e`, `.env` files) instead — directly connecting to secrets-handling lessons from other tracks (e.g., never committing credentials, echoing the Software Engineering track's environment variable practices). Fellow modifies their Activity 8.3 app to read a config value from an environment variable instead of a hardcoded value, demonstrating the same image behaving differently based on runtime configuration.
- **Rubric:**
  1. *Correct Implementation* (60%) — Is the environment variable correctly read and used at runtime?
  2. *Security Reasoning* (40%) — Is the "why not hardcode secrets" reasoning correctly explained?

---

### Activity 8.5 — Docker Volumes & Data Persistence
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 8.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn that containers are ephemeral by default (data written inside a container disappears when it's removed) and how Docker volumes solve this. Fellow runs a container with a mounted volume, writes data to it, removes and recreates the container, and demonstrates the data survives — explaining why this matters for anything stateful (like a database running in a container).
- **Rubric:**
  1. *Correct Volume Usage* (60%) — Is the volume correctly mounted and data persistence demonstrated?
  2. *Conceptual Explanation* (40%) — Is the ephemeral-container problem and volume solution correctly explained?

---

### Activity 8.6 — Multi-Container Apps with Docker Compose
- **Activity Type:** Project
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 25
- **Deadline:** 4 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.5 | **Prerequisites:** Activity 8.4, Activity 8.5
- **Evidence Required:** GitHub Repository + Screenshot
- **Review & Collaboration:** None
- **Prompt:** Learn Docker Compose for defining and running multi-container applications. Fellow writes a `docker-compose.yml` that runs their Activity 8.3 app alongside a database container (e.g., MySQL or Postgres), with proper networking between them (containers reaching each other by service name) and a persistent volume for the database, bringing the whole stack up with a single `docker-compose up`.
- **Rubric:**
  1. *Correct Compose File* (50%) — Does the multi-container setup work correctly end-to-end?
  2. *Networking & Persistence* (30%) — Do containers correctly communicate, and does data persist?
  3. *File Organization* (20%) — Is the Compose file clean and well-structured?

---

### Activity 8.7 — Debug Challenge: The Container That Won't Start
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 8.6
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given (platform-provided) a broken Dockerfile/Compose setup with 2–3 deliberate issues (a wrong `EXPOSE` port, a missing dependency in the image, an incorrect environment variable reference, a port conflict between services) and must diagnose and fix each using `docker logs`, `docker inspect`, and systematic reasoning, documenting the diagnostic process.
- **Rubric:**
  1. *Correct Diagnosis* (40%) — Are the root causes correctly identified?
  2. *Correct Fixes* (40%) — Is the container/stack genuinely working afterward?
  3. *Process Documentation* (20%) — Is the diagnostic process clearly documented?

---

### Activity 8.8 — Container Registries & Deploying to ECR
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 8.6 | **Prerequisites:** Activity 8.6
- **Evidence Required:** Screenshot + GitHub Repository
- **Review & Collaboration:** None
- **Prompt:** Learn container registries (Docker Hub, and AWS's own Elastic Container Registry). Fellow creates a private ECR repository, tags their Activity 8.3 image appropriately, authenticates and pushes it to ECR, then pulls it back down on a fresh EC2 instance to prove the image is genuinely portable — connecting containers back to the AWS infrastructure skills from Milestones 4–7. **Terminate the test instance when done.**
- **Rubric:**
  1. *Correct ECR Setup* (40%) — Is the private repository correctly created and the image correctly pushed?
  2. *Portability Demonstrated* (40%) — Is pulling and running the image on a fresh instance correctly demonstrated?
  3. *Cost Discipline* (20%) — Is the test instance correctly terminated?

---

### Activity 8.9 — Brand: My First Containerized Application
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 8.8 | **Prerequisites:** Activity 8.7, Activity 8.8
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video walking through the journey from Activity 8.1's "works on my machine" problem to a fully containerized, multi-service application pushed to ECR — a genuinely compelling before/after portfolio narrative.
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates the working containerized app?
  3. *Narrative Arc* (25%) — Does it genuinely convey the before/after journey?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 8.10 — Mock Interview: Containerization Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 8.7, Activity 8.8
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 72/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain image vs. container, why containers solve the "works on my machine" problem, walk through their Docker Compose setup, and discuss the Activity 8.7 debugging process.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 8 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 8.1 | Why Containers? | Reflection | Beginner | 5 | Required |
| 8.2 | Docker Fundamentals: Images & Containers | Workshop | Beginner | 15 | Required |
| 8.3 | Writing Your First Dockerfile | Workshop | Intermediate | 20 | Required |
| 8.4 | Environment Variables & Configuration | Workshop | Intermediate | 15 | Required |
| 8.5 | Docker Volumes & Data Persistence | Technical Research | Intermediate | 15 | Required |
| 8.6 | Multi-Container Apps with Docker Compose | Project | Advanced | 25 | Required |
| 8.7 | Debug Challenge: The Container That Won't Start | Debug Challenge | Intermediate | 20 | Required |
| 8.8 | Container Registries & Deploying to ECR | Workshop | Advanced | 20 | Required |
| 8.9 | My First Containerized Application (Brand) | Blog Post | Advanced | 15 | Required |
| 8.10 | Mock Interview: Containerization Check | Mock Interview | Advanced | 15 | Required |

**Milestone 8 total (if all completed):** 165 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–8)
- Milestones 1–7: 1,110 pts
- Milestone 8: 165 pts
- **Cumulative possible so far:** 1,275 pts

---

## What's Next: Milestone 9 Preview
**Infrastructure as Code with Terraform** — declarative infrastructure, state management, and modules. Instead of clicking through the AWS Console (as every prior Milestone has), fellows will define their infrastructure in code — reproducible, version-controlled, and reviewable via Git PRs. This is where the Cloud Engineering track's Collaborate pillar really comes alive (infrastructure code review). Say the word when ready.
