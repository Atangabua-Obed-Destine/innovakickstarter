# How we work in this repository

These rules are how reviewers can see what **each** of us did. Keep them, and your work counts.

1. **Every change starts from an issue** (a user story, a bug or a task) on the project board. Assign it to yourself
   and move it to "In progress" when you start.
2. **Never commit to `main`.** It is protected: changes arrive only through pull requests.
3. **One branch per issue**, named after it: `feature/12-checkout-409-message`, `fix/31-duplicate-rep-orders`.
4. **Small commits, clear messages**, in the present tense: "Show the 409 message at checkout", not "update".
   Commit with your own GitHub account, so your work shows under your name.
5. **Small pull requests.** Aim for under about 300 changed lines. Fill in every part of the template,
   and write `Closes #12` so the issue closes when it merges.
6. **CI must be green** before anyone approves. If CI fails, the author fixes it, not the reviewer.
7. **Review kindly and specifically**, within 24 hours of being asked. Say what you see, why it matters, and suggest a fix
   or ask a question. Mark small style points "nit:". Praise what is good. Approve only what you understand.
8. **The author answers every comment**, with a new commit or a reply, then asks for review again.
9. **Merge only with one approval** (two for changes to `contract/`, one from each part that uses it).
   The author merges, then deletes the branch.
10. **Contract changes** follow `contract/README.md`, "How to change this contract".
11. **AI tools are allowed if you say how you used them** in the pull request. Keep only code you can explain line by line.
