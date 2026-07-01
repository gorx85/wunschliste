<?php if (!defined('APP_ROOT')) die(); ?>

<div class="card">
    <h2>Registrierung</h2>
    <form method="post" action="index.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="register">

        <div class="form-group">
            <label for="reg-name">Name</label>
            <input type="text" name="reg_name" id="reg-name" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="reg-pw">Passwort</label>
            <input type="password" name="reg_passwort" id="reg-pw" required>
        </div>

        <div class="form-group">
            <label for="reg-pw2">Passwort (Kontrolle)</label>
            <input type="password" name="reg_passwort2" id="reg-pw2" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Registrieren</button>
    </form>

    <p class="text-center" style="margin-top: 1rem;">
        <a href="index.php?page=login">Bereits registriert? Einloggen</a>
    </p>
</div>
