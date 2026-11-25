# Sample pull request #14: "delivery"

**Author:** a new teammate. **Branch:** `delivery` into `main`. **Files changed:** 1.

## Description written by the author
> added delivery

## The change (`catalogue.js`)
```diff
@@ -38,6 +38,19 @@ function basketTotal(basket) {
   return basket.reduce((sum, line) => sum + line.price_fcfa * line.quantity, 0);
 }
 
+// delivery
+function totalWithDelivery(basket, branch) {
+  var t = basketTotal(basket);
+  if (branch == 'NKW') {
+    t = t + '500';
+  }
+  if (t > 50000) {
+    return t;
+  }
+  return t + 1000;
+}
+
 function orderPayload(basket, details) {
```

## What the team agreed before this PR
- Delivery costs **1,000 FCFA** for any branch.
- Delivery is **free** when the basket total is **50,000 FCFA or more**.
- Mrs. Enow Patience asked for an extra **500 FCFA** for deliveries from the Nkwen branch, because its van goes further. Mr. Asong said: "Not decided yet. Do not build it."
- Every new function needs at least one test in `catalogue.test.js`.

## Your task
Review this pull request as if it were on GitHub. Write **at least four** comments.
For each one, give the line, what you noticed, **why** it matters, and a suggestion or a question.
Include at least one sincere positive comment. End with your decision: *Approve*, *Comment* or *Request changes*, and why.
Try the code in Node if you want to check a suspicion.
