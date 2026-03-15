<?php
/*
|--------------------------------------------------------------------------
| Sendy Helper Library
|--------------------------------------------------------------------------
| Reusable functions for interacting with Sendy.
| Supports subscribe, unsubscribe, status checks, and custom fields.
|
| Usage:
| require_once __DIR__ . '/sendy.php';
|
| sendy_subscribe($email, $name);
|
*/

if (!defined('SENDY_URL')) {
    define('SENDY_URL', 'https://email.dragonsociety.com');
}

if (!defined('SENDY_API_KEY')) {
    define('SENDY_API_KEY', '');
}

if (!defined('SENDY_LIST_ID')) {
    define('SENDY_LIST_ID', '');
}


/*
|--------------------------------------------------------------------------
| Core HTTP Request
|--------------------------------------------------------------------------
*/

function sendy_request($endpoint, $data)
{
    $url = rtrim(SENDY_URL, '/') . '/' . ltrim($endpoint, '/');

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 15
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return [
            'success' => false,
            'error' => curl_error($ch)
        ];
    }

    curl_close($ch);

    return [
        'success' => true,
        'response' => trim($response)
    ];
}



|--------------------------------------------------------------------------
| Subscribe User
|--------------------------------------------------------------------------
*/

function sendy_subscribe($email, $name = '', $list_id = SENDY_LIST_ID, $custom_fields = [])
{
    $data = [
        'api_key' => SENDY_API_KEY,
        'email' => $email,
        'name' => $name,
        'list' => $list_id,
        'boolean' => 'true'
    ];

    if (!empty($custom_fields)) {
        $data = array_merge($data, $custom_fields);
    }

    return sendy_request('subscribe', $data);
}



/*
|--------------------------------------------------------------------------
| Unsubscribe User
|--------------------------------------------------------------------------
*/

function sendy_unsubscribe($email, $list_id = SENDY_LIST_ID)
{
    $data = [
        'api_key' => SENDY_API_KEY,
        'email' => $email,
        'list' => $list_id,
        'boolean' => 'true'
    ];

    return sendy_request('unsubscribe', $data);
}



/*
|--------------------------------------------------------------------------
| Check Subscriber Status
|--------------------------------------------------------------------------
|
| Returns:
| subscribed
| unsubscribed
| bounced
| complained
| not_subscribed
|
*/

function sendy_subscriber_status($email, $list_id = SENDY_LIST_ID)
{
    $data = [
        'api_key' => SENDY_API_KEY,
        'email' => $email,
        'list_id' => $list_id
    ];

    $result = sendy_request('api/subscribers/subscription-status.php', $data);

    if (!$result['success']) {
        return $result;
    }

    return $result['response'];
}



/*
|--------------------------------------------------------------------------
| Delete Subscriber
|--------------------------------------------------------------------------
*/

function sendy_delete($email, $list_id = SENDY_LIST_ID)
{
    $data = [
        'api_key' => SENDY_API_KEY,
        'email' => $email,
        'list_id' => $list_id
    ];

    return sendy_request('api/subscribers/delete.php', $data);
}



/*
|--------------------------------------------------------------------------
| Subscribe If Not Already Subscribed
|--------------------------------------------------------------------------
*/

function sendy_subscribe_safe($email, $name = '', $list_id = SENDY_LIST_ID, $custom_fields = [])
{
    $status = sendy_subscriber_status($email, $list_id);

    if ($status === 'subscribed') {
        return [
            'success' => true,
            'message' => 'Already subscribed'
        ];
    }

    return sendy_subscribe($email, $name, $list_id, $custom_fields);
}



/*
|--------------------------------------------------------------------------
| Send Campaign To Subscriber
|--------------------------------------------------------------------------
| Useful for purchased products.
|
| Requires Sendy "Trigger Campaign" plugin or API endpoint.
|--------------------------------------------------------------------------
*/

function sendy_trigger_campaign($campaign_id, $email)
{
    $data = [
        'api_key' => SENDY_API_KEY,
        'campaign_id' => $campaign_id,
        'email' => $email
    ];

    return sendy_request('api/campaigns/trigger.php', $data);
}



/*
|--------------------------------------------------------------------------
| Add Subscriber With Segmentation
|--------------------------------------------------------------------------
*/

function sendy_subscribe_segment($email, $name = '', $segment = '', $list_id = SENDY_LIST_ID)
{
    $fields = [];

    if ($segment) {
        $fields['segment'] = $segment;
    }

    return sendy_subscribe($email, $name, $list_id, $fields);
}



/*
|--------------------------------------------------------------------------
| Quick Subscribe (Minimal)
|--------------------------------------------------------------------------
*/

function sendy_quick_subscribe($email)
{
    return sendy_subscribe($email);
}
