# Responsive images, typography and accessibility: fix the delivery page

This wholesale delivery page has many problems. Some hurt phone users. Some stop disabled people from using it. Find them and fix them.

## Files
- `index.html`, `style.css`: the page to fix.
- `shopfront-480.svg`, `shopfront-960.svg`: the same placeholder picture in two sizes. Each one shows its size in red, so you can see which file the browser chose.
- `logo.svg`: the logo.

## Steps
1. Open `index.html` in Chrome or Edge. Look at it at 360px in device mode.
2. **Lighthouse:** DevTools > Lighthouse > tick only **Accessibility**, choose **Mobile**, click **Analyze page load**. Write down the score and every failed check.
3. **Keyboard test:** click the address bar, then press Tab again and again. Can you always see where you are? Can you reach and press the request button?
4. **Contrast:** check each text colour against its background at https://webaim.org/resources/contrastchecker/ . Normal text needs at least 4.5:1.
5. Fix the problems one at a time. Re-run Lighthouse after each group of fixes.
6. **Fluid image and text:** make the picture fit any screen, add a `srcset` with both files, use `rem` instead of `px` for text, and use `clamp()` for the `<h1>`.
7. In DevTools > Network, tick **Disable cache**. Reload at 360px, then at 1280px. Note which shopfront file loads each time. (A phone with a sharp screen needs more pixels, so it may choose the bigger file. That is correct.)

## Self-check
- [ ] Lighthouse Accessibility (mobile) is 90 or higher.
- [ ] With only the Tab key, I can reach every link, field and the button, and I always see a focus ring.
- [ ] Every image has a fitting `alt`, and every form field has a visible `<label>`.
- [ ] At 360px there is no sideways scroll, and the text is easy to read without zooming.
- [ ] No font size in `style.css` is in `px`.
