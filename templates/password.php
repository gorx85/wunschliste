<?php if (!defined('APP_ROOT')) die();

$user = auth_require_login();
?>

<div class="card">
    <h2>Passwort ändern</h2>
    <form method="post" action="index.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">

        <div class="form-group">
            <label for="pw-old">Altes Passwort</label>
            <input type="password" name="pwd_alt" id="pw-old" required>
        </div>

        <div class="form-group">
            <label for="pw-new">Neues Passwort</label>
            <input type="password" name="pwd_neu" id="pw-new" required>
        </div>

        <div class="form-group">
            <label for="pw-new2">Neues Passwort (Kontrolle)</label>
            <input type="password" name="pwd_neu2" id="pw-new2" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Passwort ändern</button>
    </form>
</div>
