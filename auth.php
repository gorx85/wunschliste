<?php
/**
 * Authentication Functions
 */

if (!defined('APP_ROOT')) {
    die('Direct access not allowed.');
}

function auth_start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function auth_get_current_user()
{
    auth_start_session();
    $db = Database::getInstance();
    $user = $db->fetchOne(
        "SELECT * FROM user WHERE session = ?",
        [session_id()]
    );
    return $user;
}

function auth_login($name, $password)
{
    auth_start_session();
    $db = Database::getInstance();
    $affected = $db->execute(
        "UPDATE user SET session = ? WHERE name = ? AND passwort = ?",
        [session_id(), $name, md5($password)]
    );
    return $affected > 0;
}

function auth_logout()
{
    auth_start_session();
    $db = Database::getInstance();
    $db->execute(
        "UPDATE user SET session = NULL WHERE session = ?",
        [session_id()]
    );
    session_destroy();
}

function auth_register($name, $password)
{
    $db = Database::getInstance();
    $result = $db->execute(
        "INSERT INTO user (name, passwort) VALUES (?, ?)",
        [$name, md5($password)]
    );
    return $result > 0;
}

function auth_change_password($username, $oldPassword, $newPassword)
{
    $db = Database::getInstance();
    $affected = $db->execute(
        "UPDATE user SET passwort = ? WHERE name = ? AND passwort = ?",
        [md5($newPassword), $username, md5($oldPassword)]
    );
    return $affected > 0;
}

function auth_require_login()
{
    $user = auth_get_current_user();
    if (!$user) {
        header('Location: index.php?page=login');
        exit;
    }
    return $user;
}
