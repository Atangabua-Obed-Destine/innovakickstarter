<?php
// Why you never store plain-text passwords: a small lab.
// Run it in a terminal:  php hash-lab.php
// Then answer the questions in README.md.

$password = 'Nkwen2026';

echo "1. A FAST hash (md5) of the same password, twice:\n";
echo '   '.md5($password)."\n";
echo '   '.md5($password)."\n\n";

echo "2. password_hash (bcrypt) of the same password, twice:\n";
$first = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$second = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "   $first\n";
echo "   $second\n\n";

echo "3. Checking a password against each bcrypt hash:\n";
echo '   right password, first hash:  '.var_export(password_verify($password, $first), true)."\n";
echo '   right password, second hash: '.var_export(password_verify($password, $second), true)."\n";
echo '   wrong password:              '.var_export(password_verify('nkwen2026', $first), true)."\n\n";

echo "4. How fast can an attacker guess?\n";
$start = microtime(true);
for ($i = 0; $i < 200000; $i++) {
    md5('guess'.$i);
}
$md5PerSecond = 200000 / (microtime(true) - $start);
$start = microtime(true);
for ($i = 0; $i < 5; $i++) {
    password_verify('guess'.$i, $first);
}
$bcryptPerSecond = 5 / (microtime(true) - $start);
printf("   md5:    about %s guesses per second on this laptop\n", number_format($md5PerSecond));
printf("   bcrypt: about %s guesses per second on this laptop\n\n", number_format($bcryptPerSecond, 1));

echo "5. The parts of a bcrypt hash: \$2y\$ = the algorithm, 12 = the cost,\n";
echo "   the next 22 characters = the salt, the rest = the hash itself.\n";
echo '   Salt of the first hash:  '.substr($first, 7, 22)."\n";
echo '   Salt of the second hash: '.substr($second, 7, 22)."\n";
