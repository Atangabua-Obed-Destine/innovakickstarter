<?php
// Look inside a JWT, check its signature, then try to cheat. Run: php jwt-peek.php
// TRAINING ONLY: this secret is printed here on purpose. A real secret never appears in code or Git.
$secret = 'nkwen-training-secret-do-not-reuse';
$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'
    .'.eyJzdWIiOiIxNyIsIm5hbWUiOiJOZ3dhIEJyZW5kYSIsInJvbGUiOiJjYXNoaWVyIiwiYnJhbmNoIjoiQ0FWIiwiaWF0IjoxNzk1NjAwODAwLCJleHAiOjE3OTU2MDQ0MDB9'
    .'.kzC52CxcoWrwKcta-2Ckc-UFIs2O6ijLY0z_Z3F0Y2A';

// JWTs use "base64url": base64 with - and _ instead of + and /, and no = at the end.
function b64url_decode(string $text): string
{
    return base64_decode(strtr($text, '-_', '+/'));
}

function b64url_encode(string $bytes): string
{
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

[$header, $payload, $signature] = explode('.', $token);

// TODO 1: decode the header and the payload with b64url_decode() and print them.
//         Notice: anyone who has the token can read this. A JWT is signed, not encrypted.


// TODO 2: write verify(): recompute the signature and compare.
//         The signature is b64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true)).
//         Compare with hash_equals(), never with ==.
function verify(string $header, string $payload, string $signature, string $secret): bool
{
    return false; // TODO
}

echo 'Signature valid? '.var_export(verify($header, $payload, $signature, $secret), true)."\n";

// TODO 3: cheat. Change "cashier" to "head_office" in the payload, encode it again with b64url_encode(),
//         keep the OLD signature, and check it with verify(). What happens, and why?
