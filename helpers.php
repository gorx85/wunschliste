<?php
/**
 * Helper Functions
 */

if (!defined('APP_ROOT')) {
    die('Direct access not allowed.');
}

/**
 * Format a UNIX timestamp as a readable date
 */
function format_date($timestamp, $includeTime = false)
{
    if ($timestamp === 0) {
        return '-';
    }
    if ($includeTime) {
        return date('d.m.Y, H:i', $timestamp);
    }
    return date('d.m.Y', $timestamp);
}

/**
 * Convert URLs in text to clickable links
 */
function linkify_text($text)
{
    // Decode legacy HTML entities first (old app stored &uuml; etc.)
    $text = html_entity_decode($text ?? '', ENT_QUOTES, 'UTF-8');
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    // Match URLs
    $text = preg_replace(
        '#(https?://[^\s<>]+)#i',
        '<a href="$1" target="_blank" rel="noopener">$1</a>',
        $text
    );
    // Match email addresses
    $text = preg_replace(
        '#([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})#i',
        '<a href="mailto:$1">$1</a>',
        $text
    );
    return nl2br($text);
}

/**
 * Create a clickable link from a URL, with Amazon affiliate tag
 */
function make_link($url, $label = 'Link')
{
    // Decode legacy HTML entities
    $url = html_entity_decode($url ?? '', ENT_QUOTES, 'UTF-8');

    if (empty(trim($url))) {
        return '';
    }

    // Add http:// if no protocol specified
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'http://' . $url;
    }

    // Amazon affiliate
    if (strpos($url, 'amazon.de') !== false && strpos($url, 'tag=') === false) {
        $separator = (strpos($url, '?') !== false) ? '&' : '?';
        $url .= $separator . 'tag=' . AMAZON_AFFILIATE_TAG;
    }

    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
}

/**
 * HTML-escape a string
 */
function h($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Decode legacy HTML entities and then re-escape for safe output.
 * Use this for data that was stored with htmlentities() by the old app.
 */
function h_legacy($value)
{
    // First decode any HTML entities (e.g. &uuml; -> ü)
    $decoded = html_entity_decode($value ?? '', ENT_QUOTES, 'UTF-8');
    // Then safely escape for output
    return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
}

/**
 * Set a flash message
 */
function set_message($message)
{
    auth_start_session();
    $_SESSION['flash_message'] = $message;
}

/**
 * Get and clear flash message
 */
function get_message()
{
    auth_start_session();
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

/**
 * Redirect to a URL
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get all users
 */
function get_all_users()
{
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM user ORDER BY name ASC");
}

/**
 * Get other users (excluding given name)
 */
function get_other_users($excludeName)
{
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM user WHERE name != ? ORDER BY name ASC",
        [$excludeName]
    );
}

/**
 * Clean up old deleted/received wishes (called on login)
 */
function cleanup_old_wishes()
{
    $db = Database::getInstance();
    $cutoff = time() - SESSION_LIFETIME;
    $db->execute(
        "DELETE FROM wuensche WHERE (status='geloescht' OR status='erhalten') AND timestamp < ?",
        [$cutoff]
    );
}

/**
 * Generate a CSRF token
 */
function csrf_token()
{
    auth_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verify CSRF token
 */
function csrf_verify()
{
    auth_start_session();
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
