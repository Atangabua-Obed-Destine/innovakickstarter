// Runs axe-core, the accessibility checker used inside Lighthouse and the axe DevTools extension.
import axe from 'axe-core'

// One readable line per problem, for example:
// "image-alt (critical): Images must have alternative text  ->  <img src="/photos/sugar.svg">"
export async function axeProblems(root: Element = document.body): Promise<string[]> {
  const results = await axe.run(root, {
    rules: {
      // jsdom cannot draw the page, so it cannot measure colours. Check contrast in Lighthouse instead.
      'color-contrast': { enabled: false },
    },
  })
  return results.violations.flatMap((violation) =>
    violation.nodes.map((node) => `${violation.id} (${violation.impact}): ${violation.help}  ->  ${node.html}`),
  )
}
