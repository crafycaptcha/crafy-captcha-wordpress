<?php
// Require the configuration file to load the SDK and credentials
require_once __DIR__ . '/config.php';

// Set the response type to JSON
header('Content-Type: application/json');

try {
    // Generate the encrypted iframe options using the PHP SDK.
    // This creates a unique flow with a cryptographic nonce.
    // You can customize the behavior here by passing options:
    // 'mode' => 'auto' | 'hidden' | 'puzzle'
    // 'puzzles' => ['checkbox', 'slider', 'connect']
    $encryptedIframeOptions = $global_CrafyCAPTCHA->createFlow([
        'mode' => 'auto'
    ]);

    // Return the required JSON format containing the encrypted options
    // The JS SDK expects this exact structure: { "eo": "encrypted_string_here" }
    echo json_encode(['eo' => $encryptedIframeOptions]);

} catch (Exception $e) {
    // Handle errors (e.g., storage issues, invalid keys)
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create CrafyCAPTCHA flow.']);
}
