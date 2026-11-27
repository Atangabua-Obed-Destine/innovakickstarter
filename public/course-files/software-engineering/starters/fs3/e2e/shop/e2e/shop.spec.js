// End-to-end tests: a real browser, the real React shop, the real Laravel API and database.
// Run: npx playwright test        Watch it happen: npx playwright test --headed
import { expect, test } from '@playwright/test'

// Worked example.
test('the shop shows the products from the API', async ({ page }) => {
  await page.goto('/')

  await expect(page.getByRole('heading', { name: 'Rice 50kg' })).toBeVisible()
  await expect(page.getByText('36,005 FCFA')).toBeVisible()
  await expect(page.getByRole('article')).toHaveCount(20)
})

test('changing the branch changes the stock badges', async () => {
  // TODO (README step 5): choose Mile 4 (ML4) in the "Collect from" list.
  // Then check the Rice 50kg card says "Only 3 left" and the bread button is disabled.
  throw new Error('TODO: write this test')
})

test('the basket adds up 2 x Rice 50kg and 1 x Palm Oil 5L', async () => {
  // TODO (README step 6): click the Add buttons, open the basket, check the total text.
  throw new Error('TODO: write this test')
})

test('when the API fails, the shop says so and "Try again" works', async () => {
  // TODO (README step 7): with page.route('**/api/products', ...), answer 503 while a variable "down" is true.
  // Check the alert, set down = false, click "Try again", and check a product heading appears.
  throw new Error('TODO: write this test')
})
