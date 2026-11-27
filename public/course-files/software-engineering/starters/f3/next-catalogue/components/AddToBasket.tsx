// A button the customer clicks. Clicks need the browser, so this must be a Client Component.
// TODO 6: add the line 'use client' at the very top of this file (README step 6), then:
//         keep a count with useState, and show "Add {name} to basket" and "In your basket: N".
//         Without 'use client', next build stops with an error about useState. Try it and read the message.

type Props = { name: string }

export function AddToBasket({ name }: Props) {
  return <p>TODO: the Add to basket button for {name}</p>
}
