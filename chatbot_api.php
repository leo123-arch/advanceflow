<?php
session_start();

// Load API key
$keyPath = __DIR__ . "/api-key/gemini_key.txt";
if (!file_exists($keyPath)) {
    echo json_encode(["reply" => "API key file not found"]);
    exit;
}

$apiKey = trim(file_get_contents($keyPath));
if (!$apiKey) {
    echo json_encode(["reply" => "API key is empty"]);
    exit;
}

// Read user input
$data = json_decode(file_get_contents("php://input"), true);
$question = trim($data['question'] ?? '');

if ($question === '') {
    echo json_encode(["reply" => "Please ask a question."]);
    exit;
}

/*
 ✅ WORKING GOOGLE AI MODEL
*/
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=$apiKey";

$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" =>
"You are an AI assistant for a Faculty Promotion and API Score system.
Answer clearly and simply.

Question: $question"
                ]
            ]
        ]
    ]
];

// Send request
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(["reply" => "Network error contacting Google AI"]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode !== 200) {
    echo json_encode([
        "reply" => "Google AI Error ($httpCode)",
        "debug" => $result
    ]);
    exit;
}

// Extract reply safely
$reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$reply) {
    echo json_encode([
        "reply" => "AI returned empty response",
        "debug" => $result
    ]);
    exit;
}

echo json_encode(["reply" => nl2br(trim($reply))]);
