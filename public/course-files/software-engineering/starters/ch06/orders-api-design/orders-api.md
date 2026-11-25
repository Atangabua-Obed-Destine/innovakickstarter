# Nkwen Traders Orders API: contract (draft)

Author: <your name>   Date: <date>   Status: draft for review

## 1. Who uses this API and for what
<!-- The website basket, the sales-rep app, branch managers, head office. One line each. -->

## 2. Resources
<!-- The "things" the API is about (nouns). For each: name, its fields, and an example id. -->
| Resource | Fields (name: type) | Notes |
|---|---|---|
| product | | |
| branch | | |
| order | | |
| order item | | |

## 3. Endpoints
<!-- At least 7. Method + path + what it does + success status + main error statuses. -->
| Method | Path | What it does | Success | Errors |
|---|---|---|---|---|
| GET | /api/products | | 200 | |
| | | | | |

## 4. Example: create an order
Request:
```json
POST /api/orders

```
Success response (status ___):
```json

```

## 5. Example: what goes wrong
<!-- One example body for each: 422 (invalid), 409 (not enough stock), 404 (unknown order). -->

## 6. Order statuses
<!-- Which statuses can an order have, and which changes are allowed (for example pending -> paid)? -->

## 7. Decisions and trade-offs
<!-- At least three decisions you made and why. For example: do we ever DELETE an order? Who may cancel?
     Why 409 and not 422 for low stock? How does a client avoid creating the same order twice? -->

## 8. Questions for Mr. Asong
<!-- Two things you are not sure about. -->
