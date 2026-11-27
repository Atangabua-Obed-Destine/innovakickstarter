# Problem Solving F2: Race Conditions & Stale Data

This little shop works on a fast network and breaks on a real one. There are **5 bugs**: requests that never stop, an old answer that wins the race, a page that shows the previous product, a button that orders twice, and a button that adds 1 instead of 5. The tests in `src/race.test.tsx` are correct: do not change them.

## How to work
1. `npm run dev`, open DevTools, and keep the **Network** tab open the whole time.
2. Tick "Practice: uneven network" on the shop page and type "rice" quickly. Watch the order the answers arrive in.
3. `npm test`. Fix ONE bug at a time, run the tests again, and fill in `BUGLOG.md`, including the timeline of one race.
4. `npm run typecheck` prints one warning at the start. It is a clue.

The page uses the live practice API. Bug 1 sends requests without end, and the API allows 120 a minute: stop the page (close the tab) if you see "429 Too Many Requests".

## Done when
- [ ] `npm test` shows 6 passing tests and `npm run typecheck` prints no errors.
- [ ] `BUGLOG.md` has five rows and the timeline.

## Run it
You need Node.js 22.22 or newer (`node -v`): React Router 8 asks for it.
1. Unzip this folder into your `innova-software-engineering` repository, inside `ch08-typescript/race-conditions/`.
2. Open a terminal in the folder and run `npm install --prefer-offline`.
   The first TypeScript project adds about 11 MB (the TypeScript compiler); later ones re-use npm's copy.
3. `npm run dev` starts the page at http://localhost:5173.
4. `npm test` runs the behaviour checks. `npm run typecheck` runs the type checker. Both fail at first.
