/*
 * Typing engine shared by the typing trainer, the Typing Arena and the Base Camp check.
 * The cursor only moves on the right key (a wrong key counts as an error), so speed and
 * accuracy are both honest. After Enter in code, the next line's indentation is skipped.
 * No paste, no autocorrect: every character is a real keystroke.
 */
(function () {
  'use strict';

  function escapeHtml(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function TypingEngine(text) {
    return {
      text: String(text || ''),
      pos: 0, keystrokes: 0, errors: 0, startedAt: null, endedAt: null, lastWrong: false,
      get done() { return this.pos >= this.text.length; },
      /** key: a single character, 'Enter' or 'Tab'. Returns true (right), false (wrong) or null (ignored). */
      press(key) {
        if (this.done) return null;
        if (key === 'Tab') key = ' ';
        const ch = key === 'Enter' ? '\n' : key;
        if (ch.length !== 1) return null;
        if (!this.startedAt) this.startedAt = performance.now();
        this.keystrokes++;
        if (ch === this.text[this.pos]) {
          this.pos++;
          this.lastWrong = false;
          if (ch === '\n') { while (this.text[this.pos] === ' ') this.pos++; }
          if (this.done) this.endedAt = performance.now();
          return true;
        }
        this.errors++;
        this.lastWrong = true;
        return false;
      },
      seconds() { return this.startedAt ? ((this.endedAt || performance.now()) - this.startedAt) / 1000 : 0; },
      wpm() { const s = this.seconds(); return s > 1 ? (this.pos / 5) / (s / 60) : 0; },
      accuracy() { return this.keystrokes ? ((this.keystrokes - this.errors) / this.keystrokes) * 100 : 100; },
      next() { return this.text[this.pos] || ''; },
      /** HTML of the text: typed, current (red when the last key was wrong), and still to type. */
      html() {
        const t = this.text, p = this.pos;
        const show = (s) => escapeHtml(s).replace(/\n/g, '<span class="te-nl">↵</span><br>');
        const cur = t[p] === undefined ? '' : (t[p] === '\n' ? '<span class="te-nl">↵</span><br>' : (t[p] === ' ' ? '&nbsp;' : escapeHtml(t[p])));
        return '<span class="te-done">' + show(t.slice(0, p)) + '</span>'
          + (p < t.length ? '<span class="te-cur' + (this.lastWrong ? ' te-wrong' : '') + '">' + cur + '</span>' : '')
          + '<span class="te-todo">' + show(t.slice(p + 1)) + '</span>';
      },
    };
  }

  // On-screen keyboard: which finger types which key (QWERTY).
  const ROWS = [
    ['`1234567890-=', ['lp', 'lp', 'lr', 'lm', 'li', 'li', 'ri', 'ri', 'rm', 'rr', 'rp', 'rp', 'rp']],
    ['qwertyuiop[]', ['lp', 'lr', 'lm', 'li', 'li', 'ri', 'ri', 'rm', 'rr', 'rp', 'rp', 'rp']],
    ["asdfghjkl;'", ['lp', 'lr', 'lm', 'li', 'li', 'ri', 'ri', 'rm', 'rr', 'rp', 'rp']],
    ['zxcvbnm,./', ['lp', 'lr', 'lm', 'li', 'li', 'ri', 'ri', 'rm', 'rr', 'rp']],
  ];
  const SHIFTED = { '~': '`', '!': '1', '@': '2', '#': '3', $: '4', '%': '5', '^': '6', '&': '7', '*': '8', '(': '9', ')': '0', _: '-', '+': '=',
    '{': '[', '}': ']', ':': ';', '"': "'", '<': ',', '>': '.', '?': '/' };
  const FINGER_NAMES = { lp: 'left little finger', lr: 'left ring finger', lm: 'left middle finger', li: 'left index finger',
    ri: 'right index finger', rm: 'right middle finger', rr: 'right ring finger', rp: 'right little finger', th: 'thumb' };

  function fingerFor(ch) {
    if (ch === ' ') return { key: ' ', finger: 'th', shift: false };
    if (ch === '\n') return { key: '\n', finger: 'rp', shift: false };
    let base = ch, shift = false;
    if (SHIFTED[ch]) { base = SHIFTED[ch]; shift = true; } else if (ch !== ch.toLowerCase()) { base = ch.toLowerCase(); shift = true; }
    for (const [keys, fingers] of ROWS) {
      const i = keys.indexOf(base);
      if (i >= 0) return { key: base, finger: fingers[i], shift };
    }
    return { key: base, finger: null, shift };
  }

  /** Keyboard HTML with the next key (and Shift) highlighted. */
  function keyboardHtml(nextChar) {
    const f = nextChar ? fingerFor(nextChar) : null;
    const useLeftShift = f && f.shift && f.finger && f.finger.startsWith('r');
    const useRightShift = f && f.shift && f.finger && f.finger.startsWith('l');
    let html = '';
    ROWS.forEach(([keys, fingers], r) => {
      html += '<div class="kb-row">';
      if (r === 3) html += '<span class="kb-key kb-wide' + (useLeftShift ? ' kb-next' : '') + '">Shift</span>';
      [...keys].forEach((k, i) => {
        const on = f && f.key === k;
        html += '<span class="kb-key kb-' + fingers[i] + (on ? ' kb-next' : '') + '">' + escapeHtml(k) + '</span>';
      });
      if (r === 2) html += '<span class="kb-key kb-wide' + (f && f.key === '\n' ? ' kb-next' : '') + '">Enter</span>';
      if (r === 3) html += '<span class="kb-key kb-wide' + (useRightShift ? ' kb-next' : '') + '">Shift</span>';
      html += '</div>';
    });
    html += '<div class="kb-row"><span class="kb-key kb-space kb-th' + (f && f.key === ' ' ? ' kb-next' : '') + '">space</span></div>';
    const hint = f && f.finger ? 'Next: ' + (nextChar === ' ' ? 'space' : nextChar === '\n' ? 'Enter' : '"' + nextChar + '"') + ' with your ' + FINGER_NAMES[f.finger] + (f.shift ? ' (hold Shift with the other hand)' : '') : '';
    return { html, hint };
  }

  /** Should this keydown be typed (and its default prevented)? */
  function keyOf(e) {
    if (e.ctrlKey || e.metaKey || e.altKey) return null;
    if (e.key === 'Enter' || e.key === 'Tab') return e.key;
    return e.key && e.key.length === 1 ? e.key : null;
  }

  window.TypingEngine = TypingEngine;
  window.TypingKeyboard = { html: keyboardHtml, fingerFor };
  window.typingKeyOf = keyOf;
})();
