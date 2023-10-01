<?php

// GitHub webhook secret (replace with your actual secret)
$githubSecret = 'NK_GITHUB_TO_LIVE_011023';

// Path to your Git repository on the server
$repoPath = 'https://fin.narekaltro.com/github-webhook.php';

// Log file for webhook requests (optional)
$logFile = '/path/to/log/webhook.log';

// Get the webhook payload and signature
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

// Validate the GitHub signature
if (!validateSignature($payload, $signature, $githubSecret)) {
    logRequest('Invalid GitHub signature', $logFile);
    http_response_code(400);
    die('Invalid signature.');
}

// Decode the JSON payload
$data = json_decode($payload, true);

// Check the event type
$event = $_SERVER['HTTP_X_GITHUB_EVENT'];

if ($event === 'push') {
    // Handle a push event (git pull)
    $output = shell_exec("cd $repoPath && git pull 2>&1");
    //logRequest('Git pull executed: ' . $output, $logFile);
    http_response_code(200);
    echo 'Git pull executed successfully.';
} else {
    //logRequest('Unsupported GitHub event: ' . $event, $logFile);
    http_response_code(400);
    die('Unsupported event.');
}

// Function to validate the GitHub signature
function validateSignature($payload, $signature, $secret) {
    list($algo, $hash) = explode('=', $signature, 2);
    $payloadHash = hash_hmac($algo, $payload, $secret);

    return $hash === $payloadHash;
}

// Function to log webhook requests (optional)
function logRequest($message, $logFile) {
    if ($logFile) {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
