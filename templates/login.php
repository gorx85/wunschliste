<?php if (!defined('APP_ROOT')) die(); ?>

<div class="card">
    <h2>Login</h2>
    <form method="post" action="index.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="login">

        <div class="form-group">
            <label for="login-name">Name</label>
            <select name="name" id="login-name" required>
                <option value="">— Bitte wählen —</option>
                <?php foreach (get_all_users() as $u): ?>
                <option value="<?= h($u['name']) ?>"><?= h($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="login-pw">Passwort</label>
            <div class="password-wrapper">
                <input type="password" name="passwort" id="login-pw" required>
                <button type="button" class="password-toggle" id="pwToggle" aria-label="Passwort anzeigen">👁</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Einloggen</button>
    </form>

</div>

<script>
(function() {
    var toggle = document.getElementById('pwToggle');
    var pw = document.getElementById('login-pw');
    if (toggle && pw) {
        toggle.addEventListener('click', function() {
            if (pw.type === 'password') {
                pw.type = 'text';
                toggle.textContent = '🙈';
                toggle.setAttribute('aria-label', 'Passwort verbergen');
            } else {
                pw.type = 'password';
                toggle.textContent = '👁';
                toggle.setAttribute('aria-label', 'Passwort anzeigen');
            }
        });
    }
})();
</script>
