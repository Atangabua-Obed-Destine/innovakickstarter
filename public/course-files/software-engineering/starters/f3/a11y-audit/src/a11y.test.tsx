import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { CheckoutPage } from './CheckoutPage'
import { axeProblems } from './test/axe'

// These tests describe an accessible checkout. Do not change them: change the components until they pass.
describe('Checkout page accessibility', () => {
  it('has no problems that axe can find', async () => {
    render(<CheckoutPage />)

    // If this fails, the list shows each rule, how serious it is, and the HTML that breaks it.
    expect(await axeProblems()).toEqual([])
  })

  it('every form field has a visible label', () => {
    render(<CheckoutPage />)

    expect(screen.getByLabelText('Your name')).toBeInTheDocument()
    expect(screen.getByLabelText('Phone number (MTN or Orange)')).toHaveAttribute('type', 'tel')
  })

  it('the payment methods are one named group of radio buttons', () => {
    render(<CheckoutPage />)

    const group = screen.getByRole('group', { name: 'Payment method' })
    expect(group).toContainElement(screen.getByRole('radio', { name: 'Orange Money' }))
  })

  it('the remove buttons say what they remove', () => {
    render(<CheckoutPage />)

    expect(screen.getByRole('button', { name: 'Remove Rice 50kg' })).toBeInTheDocument()
  })

  it('a keyboard user can Tab to "Add Sugar 1kg to basket" and press Enter', async () => {
    const user = userEvent.setup()
    render(<CheckoutPage />)
    const addSugar = screen.getByRole('button', { name: 'Add Sugar 1kg to basket' })

    // Press Tab until the button has the focus, like a person without a mouse.
    for (let presses = 0; presses < 30 && document.activeElement !== addSugar; presses++) {
      await user.tab()
    }
    expect(addSugar).toHaveFocus()

    await user.keyboard('{Enter}')
    expect(screen.getByText(/1 x Sugar 1kg/)).toBeInTheDocument()
  })

  it('a phone number error is announced and linked to the field', async () => {
    const user = userEvent.setup()
    render(<CheckoutPage />)

    await user.type(screen.getByLabelText('Your name'), 'Mbah Clarisse')
    await user.click(screen.getByRole('button', { name: /^Pay/ }))

    // role="alert" makes screen readers read the message out at once.
    expect(screen.getByRole('alert')).toHaveTextContent('9 digits starting with 6')
    const phone = screen.getByLabelText('Phone number (MTN or Orange)')
    expect(phone).toHaveAttribute('aria-invalid', 'true')
    expect(phone).toHaveAccessibleDescription(/9 digits starting with 6/)
  })
})
