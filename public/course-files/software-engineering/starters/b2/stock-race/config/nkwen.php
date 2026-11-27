<?php

return [
    // Pretend the till is slow (receipt printer, bad network) by waiting this many
    // milliseconds between checking the stock and saving the sale.
    // 0 in normal use. The stock race test sets it to make the race easy to see.
    'slow_till_ms' => (int) env('NKWEN_SLOW_TILL_MS', 0),
];
