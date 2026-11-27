# Test plan: whole-platform flows

These flows cross the parts of the platform. Run them on the **integrated** platform
(every part talking to the real API, on your laptops or live), not on one part alone.
Record every run, including the ones that fail. A failed run is a bug: open an issue for it.

| # | Flow | Steps | Expected | Result | Issue | Tested by | Date |
|---|---|---|---|---|---|---|---|
| 1 | Web order reaches the manager | A customer orders at Nkwen (NKW) on the web with MTN MoMo. `manager.nkw@nkwen.test` logs in. | The order is in the NKW list as `awaiting_payment`, with the total the customer saw. | | | | |
| 2 | Offline rep order syncs once | The rep app, in airplane mode, takes an order; then it goes online. | The order arrives once (not twice), with `channel: rep_app`. | | | | |
| 3 | The last items | CAV has 3 Rice 25kg. The web orders 2, then the app orders 2. | The second order gets 409 and shows "Only 1 left". Stock never goes below 0. | | | | |
| 4 | Cancel returns stock | The manager cancels an unpaid order. | Status `cancelled`; the shop shows the stock back. | | | | |
| 5 | Not allowed | The manager marks a cash order paid, then tries to cancel it. A cashier tries to cancel another order. | A clear message from the 409, and from the 403; nothing changes. | | | | |
| 6 | Staff only | Open the manager page logged out; then log in with a wrong password. | Sent to the login page; a clear error; no orders shown. | | | | |
| 7 | Slow network | Chrome DevTools "Slow 4G" (or `?delay=2000` on the stand-in). Place an order. | A loading state; the button cannot order twice. | | | | |
| 8 | Order tracking | A customer enters an order id. | The current status and total. An unknown id shows a friendly "not found". | | | | |
| 9 | TODO: a flow your squad adds | | | | | | |
| 10 | TODO: a flow your squad adds | | | | | | |

Leave out flows for parts your squad does not build, and say why in the Result column.
