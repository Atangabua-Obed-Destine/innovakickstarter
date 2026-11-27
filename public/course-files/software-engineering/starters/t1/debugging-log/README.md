# Problem Solving 110: Debugging Log

Put this folder in your own repository: `innova-software-engineering/ch10-squad-sprint/debugging-log/`.
This is individual work: every member of the squad does it alone.

## Part A: the Bafoussam incident

Message from Mr. Kenfack Roland, manager at Bafoussam Centre, forwarded by Mr. Asong:

> "Yesterday our rep synced his orders at the market, where the network is weak. The app said
> 'All orders sent!'. This morning Tchoupo Wholesale received TWO deliveries of 10 bags of Rice 50kg
> and wants to pay for one. Dschang Mini-Market, who ordered 30 bags, received nothing, and nobody
> called them. We are nearly out of rice. What happened?"

`sync.js` is a JavaScript copy of the rep app's sync code, so everyone can debug it, whatever their pathway.
`fake-api.js` is a pretend Orders API with the contract's rules; it can lose an answer, like a weak network.

| File | What it is | Change it? |
|---|---|---|
| `sync.js` | The sync code with the bug(s) | Yes: this is where you fix it |
| `sync.test.js` | 5 tests; 3 fail at the start | You may ADD tests; do not weaken these |
| `fake-api.js` | The pretend API | No |
| `run-incident.js` | Replays the day of the incident | No |
| `DEBUGGING_LOG.md` | Your log | Yes: fill it in |

Commands (from this folder, Node 22):
```
node run-incident.js     # see what happened
node --test              # 2 pass, 3 fail at the start
```

## Part B: two real bugs from your squad

In the same log, write up two real bugs you met yourself during the sprint (in your part, or between parts),
with links to the issue, the pull request that fixed it and, if there was one, the failing CI run.
