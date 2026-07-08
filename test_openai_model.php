<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing OpenAI Configuration\n";
echo "============================\n\n";

// Check environment variable directly
$envModel = env('OPENAI_MODEL');
$configModel = config('services.openai.model');

echo "ENV OPENAI_MODEL: " . ($envModel ?: 'NOT SET') . "\n";
echo "Config Model: " . ($configModel ?: 'NOT SET') . "\n\n";

// Test the service
$service = new \App\Services\LiveAIInterviewService();
echo "Service configured: " . ($service->isConfigured() ? 'YES' : 'NO') . "\n\n";

// Make a simple test call
echo "Testing API call...\n";

$apiKey = config('services.openai.key');
$model = config('services.openai.model');

$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => 'Bearer ' . $apiKey,
    'Content-Type' => 'application/json',
])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
    'model' => $model,
    'messages' => [
        ['role' => 'user', 'content' => 'Say "Hello, interview ready!" in exactly those words.']
    ],
    'max_tokens' => 50,
]);

if ($response->successful()) {
    echo "SUCCESS!\n";
    echo "Model used: $model\n";
    echo "Response: " . $response->json('choices.0.message.content') . "\n";
} else {
    echo "FAILED!\n";
    echo "Status: " . $response->status() . "\n";
    echo "Error: " . $response->body() . "\n";
}
