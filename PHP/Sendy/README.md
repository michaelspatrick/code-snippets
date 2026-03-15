
# Sendy PHP Helper Library

A reusable PHP helper library for interacting with a self-hosted Sendy installation.

This library simplifies common Sendy tasks such as:
- subscribing users
- unsubscribing users
- checking subscriber status
- safely subscribing without duplicates
- passing custom fields
- optionally triggering campaigns

It works well with standalone PHP apps, WordPress plugins, funnels, contest systems, and seminar registration tools.

---

## Installation

Copy `sendy.php` into your project:

/lib/sendy.php

Then include it in your PHP file:

```php
require_once __DIR__ . '/lib/sendy.php';
```

---

## Configuration

At the top of `sendy.php`, configure your Sendy install:

```php
define('SENDY_URL', 'https://email.dragonsociety.com');
define('SENDY_API_KEY', 'your_api_key');
define('SENDY_LIST_ID', 'your_list_id');
```

---

## Basic Usage

### Subscribe a user

```php
$result = sendy_subscribe('student@example.com', 'John Smith');

if ($result['success']) {
    echo $result['response'];
} else {
    echo 'Error: ' . $result['error'];
}
```

---

### Subscribe with custom fields

```php
$result = sendy_subscribe(
    'student@example.com',
    'John Smith',
    SENDY_LIST_ID,
    [
        'event_name' => 'SWARM 2026',
        'winner_level' => 'Gold'
    ]
);
```

---

### Unsubscribe a user

```php
sendy_unsubscribe('student@example.com');
```

---

### Check subscriber status

```php
$status = sendy_subscriber_status('student@example.com');
echo $status;
```

---

### Safe subscribe (avoid duplicates)

```php
sendy_subscribe_safe('student@example.com', 'John Smith');
```

---

### Delete a subscriber

```php
sendy_delete('student@example.com');
```

---

### Quick subscribe

```php
sendy_quick_subscribe('student@example.com');
```

---

## Example: Seminar Registration

```php
require_once __DIR__ . '/lib/sendy.php';

$result = sendy_subscribe(
    $email,
    $name,
    SENDY_LIST_ID,
    [
        'event_name' => 'Smoky Mountain SWARM',
        'event_date' => '2026-04-10',
        'source' => 'seminar_registration'
    ]
);
```

---

## Example: Contest Entry

```php
sendy_subscribe(
    $email,
    $name,
    SENDY_LIST_ID,
    [
        'event_name' => 'SWARM Durham',
        'winner_level' => 'Silver',
        'source' => 'contest_entry'
    ]
);
```

---

## Requirements

- PHP 7.4+ recommended
- cURL extension enabled
- Working Sendy installation
- Valid API key and list ID

---

## Security Notes

- Do not commit API keys to public repositories
- Use environment variables or local config files
- Validate and sanitize user input before sending to Sendy

Example:

```php
define('SENDY_API_KEY', getenv('SENDY_API_KEY'));
```

---

## Suggested Improvements

Possible additions to the library:

- event-specific helper functions
- retry logic
- logging wrappers
- SQLite or MySQL logging of subscriptions
- webhook verification helpers

---

## License

Use freely in personal or commercial projects.
