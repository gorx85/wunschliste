<?php
/**
 * Application Configuration
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not allowed.');
}

// Debug mode
define('DEBUG', false);

// Database credentials (loaded from local file, not committed to git)
$_local_config = __DIR__ . '/config.local.php';
if (!file_exists($_local_config)) {
    die('config.local.php not found. Copy config.local.php.example to config.local.php and fill in your credentials.');
}
require $_local_config;
unset($_local_config);

// Session lifetime (60 days)
define('SESSION_LIFETIME', 86400 * 60);

// Amazon affiliate tag
define('AMAZON_AFFILIATE_TAG', 'gorxat-21');

// Pages accessible without login
$PAGES_PUBLIC = ['login', 'register'];

// Priority levels
$PRIORITIES = [
    1 => '1 - ganz dringend',
    2 => '2 - eher dringend',
    3 => '3 - so halb dringend',
    4 => '4 - nicht so dringend',
    5 => '5 - gar nicht dringend',
];

// Status labels
$STATUS_LABELS = [
    'gewuenscht' => 'aktiver Wunsch',
    'erhalten'   => 'erhalten',
    'geloescht'  => 'gelöscht',
    'locked'     => 'reserviert',
    'unlocked'   => 'nicht reserviert',
];
