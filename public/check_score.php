<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$calc = app()->make(App\Services\CareerCapitalCalculator::class);
$user = App\Models\User::where('email', 'neo@gmail.com')->first();
$track = $user->fellowTracks()->first()->track;

$calc->updateScore($user, $track);
echo "Recalculated Score: " . $user->fellowTracks()->first()->score . "\n";
