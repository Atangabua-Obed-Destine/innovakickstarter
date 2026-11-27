// Project end-to-end tests: logging in, ordering and cancelling, through the real API and MySQL.
// They use the demo accounts seeded by StaffSeeder (password Nkwen-Demo-2026).
import { expect, test } from '@playwright/test'

async function logIn(page, email, password = 'Nkwen-Demo-2026') {
  await page.getByLabel('Email').fill(email)
  await page.getByLabel('Password').fill(password)
  await page.getByRole('button', { name: 'Log in' }).click()
}

// Worked example.
test('a wrong password shows the API message', async ({ page }) => {
  await page.goto('/')
  await page.getByRole('button', { name: 'Branch orders' }).click()

  await logIn(page, 'cashier.nkw@nkwen.test', 'wrong-password-1')

  await expect(page.getByRole('alert')).toHaveText('Wrong email or password.')
})

test('a new cashier signs up, places an order, and cannot cancel it', async () => {
  // TODO: sign up with a new email each run (`e2e+${Date.now()}@nkwen.test`) at CAV,
  // order 2 x Rice 50kg + 1 x Palm Oil 5L for "Mbah Clarisse", check the total in the confirmation,
  // then check the order is in Branch orders with no Cancel button.
  throw new Error('TODO: write this test')
})

test('the branch manager cancels an order, and stays logged in after a refresh', async () => {
  // TODO: log in as manager.nkw@nkwen.test, place a cash order at NKW, reload the page,
  // open Branch orders, cancel that order and check "Status: Cancelled".
  throw new Error('TODO: write this test')
})
