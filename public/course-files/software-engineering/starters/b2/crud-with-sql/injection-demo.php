<?php
// SQL injection, seen with your own eyes. Run:  php injection-demo.php
// It searches products by name twice: once the WRONG way, once the RIGHT way.
// Change these if your MySQL user or password is different.
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nkwen_sql;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Imagine a customer typed this into the shop's search box:
$typed = "zzz' OR '1'='1";

// WRONG: the typed text is glued into the SQL. The quote in it ends our string early,
// and "OR '1'='1'" becomes part of the query: it is true for every row.
$sql = "SELECT name, price_fcfa FROM products WHERE name = '$typed'";
echo "WRONG query: $sql\n";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo 'WRONG way found '.count($rows)." products\n\n";

// RIGHT: a prepared statement. The ? is a placeholder. MySQL receives the text separately,
// as data only, so it can never change the query.
$stmt = $pdo->prepare('SELECT name, price_fcfa FROM products WHERE name = ?');
$stmt->execute([$typed]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "RIGHT query: SELECT name, price_fcfa FROM products WHERE name = ?\n";
echo 'RIGHT way found '.count($rows)." products\n";
