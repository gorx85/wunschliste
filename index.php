<?php
/**
 * Wunschliste - Main Entry Point / Router
 */

define('APP_ROOT', __DIR__);

require_once 'config.php';
require_once 'database.php';
require_once 'auth.php';
require_once 'helpers.php';

auth_start_session();

// Determine current page
$page = $_GET['page'] ?? 'wishes';
$user = auth_get_current_user();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!csrf_verify() && $action !== '') {
        set_message('Ungültige Anfrage. Bitte versuche es erneut.');
        redirect('index.php');
    }

    switch ($action) {
        case 'login':
            $name = trim($_POST['name'] ?? '');
            $password = $_POST['passwort'] ?? '';
            if (auth_login($name, $password)) {
                cleanup_old_wishes();
                redirect('index.php');
            } else {
                set_message('Login fehlgeschlagen!');
                redirect('index.php?page=login');
            }
            break;

        case 'register':
            $name = trim($_POST['reg_name'] ?? '');
            $pw1 = $_POST['reg_passwort'] ?? '';
            $pw2 = $_POST['reg_passwort2'] ?? '';
            if ($name === '') {
                set_message('Du hast keinen Namen eingegeben!');
            } elseif ($pw1 === '') {
                set_message('Du hast kein Passwort angegeben!');
            } elseif ($pw1 !== $pw2) {
                set_message('Die beiden Passwörter stimmen nicht überein!');
            } elseif (!auth_register($name, $pw1)) {
                set_message('Name existiert bereits!');
            } else {
                set_message('Registrierung erfolgreich! Du kannst dich jetzt einloggen.');
            }
            redirect('index.php?page=register');
            break;

        case 'logout':
            auth_logout();
            redirect('index.php?page=login');
            break;

        case 'change_password':
            $user = auth_require_login();
            $old = $_POST['pwd_alt'] ?? '';
            $new = $_POST['pwd_neu'] ?? '';
            $new2 = $_POST['pwd_neu2'] ?? '';
            if ($new !== $new2) {
                set_message('Neues Passwort und Kontrolleingabe stimmen nicht überein!');
            } elseif (!auth_change_password($user['name'], $old, $new)) {
                set_message('Altes Passwort falsch!');
            } else {
                set_message('Passwort wurde geändert!');
            }
            redirect('index.php?page=password');
            break;

        case 'save_wish':
            $user = auth_require_login();
            $db = Database::getInstance();
            $titel = trim($_POST['wunsch_titel'] ?? '');
            $text = trim($_POST['wunsch_text'] ?? '');
            $link = trim($_POST['wunsch_link'] ?? '');
            $prioritaet = (int) ($_POST['wunsch_prioritaet'] ?? 3);
            $id = (int) ($_POST['wunsch_id'] ?? 0);
            $zeit = time();

            if ($prioritaet < 1 || $prioritaet > 5) {
                $prioritaet = 3;
            }

            if ($id > 0) {
                // Update
                $affected = $db->execute(
                    "UPDATE wuensche SET titel=?, text=?, prioritaet=?, link=?, timestamp=? WHERE id=? AND name=?",
                    [$titel, $text, $prioritaet, $link, $zeit, $id, $user['name']]
                );
                set_message($affected ? 'Wunsch wurde geändert!' : 'Ein Fehler ist aufgetreten!');
            } else {
                // Insert
                $affected = $db->execute(
                    "INSERT INTO wuensche (titel, text, prioritaet, link, name, timestamp) VALUES (?, ?, ?, ?, ?, ?)",
                    [$titel, $text, $prioritaet, $link, $user['name'], $zeit]
                );
                set_message($affected ? 'Wunsch wurde eingetragen!' : 'Ein Fehler ist aufgetreten!');
            }
            redirect('index.php');
            break;

        case 'save_comment':
            $user = auth_require_login();
            $db = Database::getInstance();
            $wunsch_id = (int) ($_POST['kom_id'] ?? 0);
            $text = trim($_POST['kom_text'] ?? '');
            $zeit = time();

            if ($text !== '' && $wunsch_id > 0) {
                $db->execute(
                    "INSERT INTO kommentare (wunsch_id, name, text, timestamp) VALUES (?, ?, ?, ?)",
                    [$wunsch_id, $user['name'], $text, $zeit]
                );
                set_message('Kommentar wurde gespeichert!');
            }
            redirect('index.php?page=details&id=' . $wunsch_id);
            break;

        case 'change_status':
            $user = auth_require_login();
            $db = Database::getInstance();
            $id = (int) ($_POST['wish_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $zeit = time();

            $validStatuses = ['gewuenscht', 'erhalten', 'geloescht'];
            if (in_array($status, $validStatuses) && $id > 0) {
                $affected = $db->execute(
                    "UPDATE wuensche SET status=?, timestamp=? WHERE id=? AND name=?",
                    [$status, $zeit, $id, $user['name']]
                );
                if ($status === 'geloescht') {
                    set_message($affected ? 'Wunsch wurde gelöscht!' : 'Status konnte nicht geändert werden!');
                } else {
                    set_message($affected ? 'Status wurde geändert!' : 'Status konnte nicht geändert werden!');
                }
            }
            redirect($_POST['redirect'] ?? 'index.php');
            break;

        case 'toggle_reservation':
            $user = auth_require_login();
            $db = Database::getInstance();
            $id = (int) ($_POST['wish_id'] ?? 0);
            $lock = $_POST['lock'] ?? '';
            $redirectTo = $_POST['redirect'] ?? 'index.php';

            if ($lock === 'lock' && $id > 0) {
                $affected = $db->execute(
                    "UPDATE wuensche SET status2='locked', reserviert=? WHERE id=? AND name!=? AND status2='unlocked'",
                    [$user['name'], $id, $user['name']]
                );
                set_message($affected ? 'Wunsch wurde reserviert!' : 'Reservierung konnte nicht geändert werden!');
            } elseif ($lock === 'unlock' && $id > 0) {
                $affected = $db->execute(
                    "UPDATE wuensche SET status2='unlocked', reserviert='' WHERE id=? AND reserviert=?",
                    [$id, $user['name']]
                );
                set_message($affected ? 'Reservierung wurde aufgehoben!' : 'Reservierung konnte nicht geändert werden!');
            }
            redirect($redirectTo);
            break;
    }
    exit;
}

// Require login for all pages except public ones
if (!$user && !in_array($page, $PAGES_PUBLIC)) {
    $page = 'login';
}

// Get other users for navigation
$otherUsers = $user ? get_other_users($user['name']) : [];
$flashMessage = get_message();

// Include layout
include 'templates/layout.php';
