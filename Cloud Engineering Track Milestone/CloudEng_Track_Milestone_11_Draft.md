# I-NNOVA KICKSTARTER — Cloud Engineering Track
## Milestone 11: Kubernetes Introduction

**Status:** Draft for review
**Unlocks:** After Milestone 10 is fully completed
**Theme:** Core Kubernetes concepts (pods, deployments, services) and deploying to a managed Kubernetes service (Amazon EKS). Deliberately kept at foundations-depth — enough to deploy and understand a real application on K8s — with deeper orchestration mastery (Helm, operators, service mesh, multi-cluster) reserved for a later specialization branch.
**Target fellow:** Docker-fluent, has built a full CI/CD pipeline; has never touched Kubernetes.

---

### Activity 11.1 — Why Kubernetes? Beyond Docker Compose
- **Activity Type:** Reflection
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 5
- **Deadline:** 2 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 10.8
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow reflects on Milestone 8's Docker Compose setup — it worked for one machine, but what happens if that machine fails, or traffic outgrows one machine's capacity? Fellow researches what Kubernetes adds beyond Docker Compose (scheduling containers across multiple machines, self-healing, rolling updates, service discovery at scale) and explains when a team would genuinely need it versus when Compose is still perfectly sufficient (an important, often-skipped nuance — K8s is not always the right tool).
- **Rubric:**
  1. *Understanding* (60%) — Is the Compose-vs-Kubernetes distinction correctly explained?
  2. *Balanced Judgment* (40%) — Is the "when NOT to use K8s" reasoning genuinely sound, not just K8s-hype?

---

### Activity 11.2 — Kubernetes Core Concepts: Pods, Deployments, Services
- **Activity Type:** Technical Research
- **Pillar:** Build
- **Difficulty:** Beginner
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** Activity 11.1 | **Prerequisites:** Activity 11.1
- **Evidence Required:** Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn the core object model: a Pod (one or more containers, the smallest deployable unit), a Deployment (manages desired-state Pod replicas, handles rolling updates — conceptually similar to Milestone 5's Auto Scaling Group), and a Service (stable networking/load balancing across Pods, since Pods are ephemeral and get new IPs constantly). Fellow explains each concept and explicitly connects Deployments to the ASG concept and Services to the ALB concept from Milestone 5 — the parallel is deliberate and worth drawing out.
- **Rubric:**
  1. *Conceptual Accuracy* (60%) — Are Pods/Deployments/Services correctly explained?
  2. *Cross-Concept Connection* (40%) — Is the ASG/ALB parallel correctly drawn?

---

### Activity 11.3 — Local Kubernetes with Minikube
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 11.2 | **Prerequisites:** Activity 11.2
- **Evidence Required:** Screenshot
- **Review & Collaboration:** None
- **Prompt:** Install Minikube (a local, single-node Kubernetes cluster — free, zero AWS cost, ideal for learning the basics before touching real cloud resources) and `kubectl`. Fellow deploys their Milestone 8 containerized application to Minikube using a basic Deployment and Service YAML, verifies it's running and accessible, and screenshots `kubectl get pods`, `kubectl get deployments`, and `kubectl get services` all showing the running application.
- **Rubric:**
  1. *Correct Deployment* (60%) — Is the application correctly deployed and running on Minikube?
  2. *Correct Verification* (40%) — Are the `kubectl get` commands correctly used to confirm state?

---

### Activity 11.4 — Scaling & Self-Healing in Kubernetes
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** Activity 11.3 | **Prerequisites:** Activity 11.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn `kubectl scale` and Kubernetes' built-in self-healing (a Deployment automatically replacing a failed Pod, echoing Milestone 5's ASG self-healing demonstration). Fellow scales their Activity 11.3 Deployment to 3 replicas, verifies all 3 are running, then manually deletes one Pod (`kubectl delete pod`) and observes Kubernetes automatically create a replacement to maintain the desired replica count.
- **Rubric:**
  1. *Correct Scaling* (40%) — Is the Deployment correctly scaled to 3 replicas?
  2. *Self-Healing Demonstrated* (60%) — Is automatic Pod replacement correctly shown after manual deletion?

---

### Activity 11.5 — ConfigMaps & Secrets
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Intermediate
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 11.3
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Learn ConfigMaps (non-sensitive configuration) and Secrets (sensitive values, base64-encoded but NOT encrypted by default — an important nuance fellows must understand, not a false sense of security) as Kubernetes' equivalents to Milestone 8's Docker environment variables. Fellow creates a ConfigMap and a Secret, mounts both into their Activity 11.3 Deployment as environment variables, and writes a short note on why Kubernetes Secrets alone aren't sufficient security (they need additional encryption-at-rest configuration in a real cluster) — avoiding the common misconception that "Secret" means "encrypted."
- **Rubric:**
  1. *Correct Implementation* (50%) — Are ConfigMap and Secret correctly created and mounted?
  2. *Security Nuance Understanding* (50%) — Is the "Secrets aren't automatically encrypted" nuance correctly explained?

---

### Activity 11.6 — Deploying to Amazon EKS
- **Activity Type:** Workshop
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 30
- **Deadline:** 5 days | **Grace Period:** 2 days | **Late Penalty:** 15%
- **Chain Parent:** Activity 11.4 | **Prerequisites:** Activity 11.4, Activity 11.5
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Move from local Minikube to a real managed cluster: fellow provisions an EKS cluster (this is a genuinely more expensive resource than most prior Milestones' work — **read the cost warning below carefully**), deploys their Milestone 8 application (pulling the image from their Milestone 8/10 ECR repository, tying the whole track together), and verifies it's accessible via the Service's external endpoint. **Delete the EKS cluster and all associated resources (node groups, load balancers) immediately after verification — EKS control plane costs accrue per hour regardless of usage.**
- **Rubric:**
  1. *Correct EKS Deployment* (40%) — Is the cluster correctly provisioned and the application correctly deployed?
  2. *ECR Integration* (25%) — Is the image correctly pulled from the fellow's own ECR repository?
  3. *Verified Accessibility* (15%) — Is the application correctly reachable via the Service endpoint?
  4. *Complete Cleanup* (20%) — Is the EKS cluster and all associated resources (especially any auto-created Load Balancers) fully deleted, with explicit confirmation?

> **⚠️ Cost Warning:** EKS is one of the more expensive services fellows will touch in this track — the control plane alone has an hourly charge with no meaningful free tier, and worker nodes (EC2 instances) and any Load Balancers created by Kubernetes Services add further cost. This activity should have the tightest possible time window and the most explicit teardown verification of anything in the track so far. Strongly recommend the platform require a screenshot of the AWS EKS console showing **zero active clusters** as final proof, not just a claim of deletion.

---

### Activity 11.7 — Debug Challenge: The Crash-Looping Pod
- **Activity Type:** Debug Challenge
- **Pillar:** Build
- **Difficulty:** Advanced
- **Base Points:** 20
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 15%
- **Chain Parent:** None | **Prerequisites:** Activity 11.4
- **Evidence Required:** Screenshot + Written Submission
- **Review & Collaboration:** None
- **Prompt:** Fellow is given (on Minikube, no AWS cost involved) a Deployment YAML with a deliberate issue causing the Pod to enter `CrashLoopBackOff` (a missing ConfigMap reference, an incorrect command, a health check that's misconfigured). Fellow uses `kubectl describe pod` and `kubectl logs` to diagnose the root cause and fix it — the single most common real-world Kubernetes troubleshooting scenario.
- **Rubric:**
  1. *Correct Diagnostic Commands* (30%) — Are `describe`/`logs` correctly used to gather information?
  2. *Correct Diagnosis* (35%) — Is the root cause correctly identified?
  3. *Correct Fix* (35%) — Is the Pod genuinely running healthily afterward?

---

### Activity 11.8 — Brand: My First Kubernetes Deployment
- **Activity Type:** Blog Post
- **Pillar:** Brand
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 2 days | **Late Penalty:** 10%
- **Chain Parent:** Activity 11.6 | **Prerequisites:** Activity 11.6, Activity 11.7
- **Evidence Required:** URL/Link **AND** Video Recording
- **Review & Collaboration:** None
- **Prompt:** Write an article and record a video covering the journey from local Minikube experimentation to a real EKS deployment — including an honest note on the cost-awareness required (this is a genuinely valuable, differentiating thing to demonstrate publicly: an engineer who thinks about cost, not just capability).
- **Rubric:**
  1. *Report Quality* (30%) — Clear, thorough, well-structured?
  2. *Video Quality* (30%) — Clear verbal explanation, demonstrates the deployment?
  3. *Cost-Awareness Demonstrated* (25%) — Does the piece genuinely reflect the cost discipline required?
  4. *Authenticity* (15%) — Own words/voice?

---

### Activity 11.9 — Mock Interview: Kubernetes Fundamentals Check
- **Activity Type:** Mock Interview
- **Pillar:** Interview
- **Difficulty:** Advanced
- **Base Points:** 15
- **Deadline:** 3 days | **Grace Period:** 1 day | **Late Penalty:** 10%
- **Chain Parent:** None | **Prerequisites:** Activity 11.7, Activity 11.8
- **Evidence Required:** Interview Session
- **Interview Mode:** AI Interview
- **Passing Score:** 75/100
- **Required Sessions:** 1
- **Review & Collaboration:** None
- **Focus:** Explain Pods vs. Deployments vs. Services, when Kubernetes is (and isn't) the right tool, walk through the Activity 11.7 CrashLoopBackOff debugging process, and discuss cost management specific to EKS.
- **Rubric:** System-scored by AI interview module against passing score.

---

## Milestone 11 Summary
| # | Activity | Activity Type | Difficulty | Points | Required? |
|---|----------|---------------|------------|--------|-----------|
| 11.1 | Why Kubernetes? Beyond Docker Compose | Reflection | Beginner | 5 | Required |
| 11.2 | Kubernetes Core Concepts | Technical Research | Beginner | 15 | Required |
| 11.3 | Local Kubernetes with Minikube | Workshop | Intermediate | 20 | Required |
| 11.4 | Scaling & Self-Healing in Kubernetes | Workshop | Intermediate | 20 | Required |
| 11.5 | ConfigMaps & Secrets | Workshop | Intermediate | 15 | Required |
| 11.6 | Deploying to Amazon EKS ⚠️ | Workshop | Advanced | 30 | Required |
| 11.7 | Debug Challenge: The Crash-Looping Pod | Debug Challenge | Advanced | 20 | Required |
| 11.8 | My First Kubernetes Deployment (Brand) | Blog Post | Advanced | 15 | Required |
| 11.9 | Mock Interview: Kubernetes Fundamentals Check | Mock Interview | Advanced | 15 | Required |

**Milestone 11 total (if all completed):** 155 points (raw, pre-multiplier)

---

## Running Track Total (Milestones 1–11)
- Milestones 1–10: 1,625 pts
- Milestone 11: 155 pts
- **Cumulative possible so far:** 1,780 pts

---

## Critical Cost Discipline Flag
**Activity 11.6 (EKS) deserves special platform attention.** Unlike everything else in this track so far, EKS has no meaningful free tier — the control plane bills hourly from the moment of creation regardless of what's deployed on it. I'd strongly recommend:
1. A tight, enforced time window for this specific activity (e.g., a hard 4-hour "lab session" concept rather than the multi-day deadlines used elsewhere).
2. Mandatory proof-of-deletion (empty cluster list screenshot) before the activity can be marked complete.
3. Possibly an automated cleanup script/Lambda the platform runs as a safety net after a fixed time window, independent of whether the fellow remembered to delete it — this is worth a serious infrastructure conversation given real financial exposure.

---

## What's Next: Milestone 12 Preview
**Monitoring, Logging & Observability** — CloudWatch, centralized logging, and alerting, so fellows can actually see what their infrastructure is doing rather than just hoping it works. Say the word when ready.
