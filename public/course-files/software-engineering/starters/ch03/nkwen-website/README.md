# Project: the Nkwen Traders website

**Brief from Mr. Asong Bertrand, Head of Engineering:** most of our customers will open this site on a phone, often a cheap one on costly data. Build a one-page website that loads fast, reads well on a 360px screen and works for everyone, including people who use a keyboard or a screen reader. No JavaScript is needed yet.

## Files
- `index.html`: the page skeleton with TODOs.
- `style.css`: brand colours (contrast already checked) and TODOs.
- `content.md`: the real branches, products, prices and payment methods. Use only this data.
- `logo.svg`: the logo.

## User stories (the reviewer checks every one)
1. On a 360px-wide phone there is no sideways scroll, and body text is at least 16px (1rem).
2. As a customer, I see all 20 products, grouped by their 7 categories, each with its price in FCFA written like `36,005 FCFA` and its unit.
3. The products show in 1 or 2 columns at 360px and at least 3 columns at 1280px.
4. I see the 4 open branches with city, manager and the year they opened. Limbe Down Beach is shown as **not open yet (2027)**, in words.
5. I see the 4 ways to pay: cash, MTN Mobile Money, Orange Money and bank transfer.
6. The menu links jump to Products, Branches, How to pay and Contact.
7. With only the keyboard (Tab, Shift+Tab, Enter), I can reach every link, field and the button, and I can always see where the focus is. The first Tab shows a "Skip to main content" link.
8. Every form field has a visible label. The branch list offers only the 4 open branches. The phone field uses `type="tel"`.
9. The logo has useful alt text. There is exactly one `<h1>`, and headings go in order (h1, h2, h3, h4).
10. Lighthouse Accessibility (Mobile) is **90 or higher**.
11. The site is live on GitHub Pages and looks deliberate at 360px, 768px and 1280px.

## Steps
1. In your `innova-software-engineering` repository, create the folder `ch03-web-pages/nkwen-website/` and copy these files into it.
2. Build the HTML section by section from `content.md`. Validate it at https://validator.w3.org/#validate_by_input .
3. Write the phone CSS first. Then add the two media queries.
4. Test every user story. Fix, then test again.
5. Commit and push. Turn on GitHub Pages for the repository (Settings > Pages > main > / (root)). Your site will be at `https://<your-username>.github.io/innova-software-engineering/ch03-web-pages/nkwen-website/`.

## Self-check
- [ ] I ticked all 11 user stories myself on the live site, not only on my laptop.
- [ ] Lighthouse Accessibility (Mobile) is 90 or higher on the live site.
- [ ] The HTML validator shows no errors.
- [ ] I can explain every line of my code, including any line an AI tool suggested.
