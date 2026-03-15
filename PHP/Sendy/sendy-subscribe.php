<?php
// --- Sendy subscribe helper ---
function sendy_subscribe(array $config, string $name, string $email, bool $gdpr = false, bool $doubleOptIn = false): array {
    $sendyUrl   = rtrim($config['sendy_url'] ?? '', '/');
    $apiKey     = trim($config['sendy_api_key'] ?? '');
    $listId     = trim($config['sendy_list_id'] ?? '');

    if ($sendyUrl === '' || $apiKey === '' || $listId === '') {
        return ['ok' => false, 'msg' => 'Sendy is not configured for this profile.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'msg' => 'Invalid email address.'];
    }

    // Sendy expects URL-encoded form POST
    $endpoint = $sendyUrl . '/subscribe';

    $post = [
        'api_key' => $apiKey,
        'list'    => $listId,
        'email'   => $email,
        'name'    => $name,
        // Sendy flags:
        // boolean-like values as 1/0 strings are safest
        'gdpr'    => $gdpr ? 'true' : 'false',
        'boolean' => 'true', // makes Sendy return "1" or an error string
    ];

    // Double opt-in:
    // If you want immediate subscribe, set to 0
    // If you want confirmation email, set to 1
    $post['double_opt_in'] = $doubleOptIn ? '1' : '0';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($post),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return ['ok' => false, 'msg' => 'Sendy request failed: ' . $err];
    }

    $resp = trim((string)$resp);

    // When boolean=true, Sendy returns:
    // "1" => success
    // "Already subscribed." => not fatal
    // otherwise => error message
    if ($code >= 200 && $code < 300) {
        if ($resp === '1') {
            return ['ok' => true, 'msg' => 'Subscribed'];
        }
        if (stripos($resp, 'already subscribed') !== false) {
            return ['ok' => true, 'msg' => 'Already subscribed'];
        }
        return ['ok' => false, 'msg' => $resp ?: 'Unknown Sendy response'];
    }

    return ['ok' => false, 'msg' => "HTTP $code: " . ($resp ?: 'Unknown error')];
}
