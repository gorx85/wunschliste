<?php if (!defined('APP_ROOT')) die();

$user = auth_require_login();
$db = Database::getInstance();

$wunsch = null;
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $wunsch = $db->fetchOne(
        "SELECT * FROM wuensche WHERE id = ? AND name = ?",
        [$id, $user['name']]
    );
}

$titel = $wunsch ? 'Wunsch ändern' : 'Neuen Wunsch erstellen';
$button = $wunsch ? 'Wunsch ändern' : 'Wunsch erstellen';
?>

<?php if ($wunsch): ?>
<a href="<?= h($_GET['back'] ?? 'index.php') ?>" class="back-link">← Zurück</a>
<?php endif; ?>
<h2><?= $titel ?></h2>
<div class="card">
    <form method="post" action="index.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_wish">
        <input type="hidden" name="wunsch_id" value="<?= $wunsch ? (int)$wunsch['id'] : 0 ?>">

        <div class="form-group">
            <label for="w-titel">Wunsch (kurzer Titel)</label>
            <input type="text" name="wunsch_titel" id="w-titel" maxlength="50" required
                   value="<?= h_legacy($wunsch['titel'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="w-prio">Priorität</label>
            <select name="wunsch_prioritaet" id="w-prio">
                <?php foreach ($PRIORITIES as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($wunsch && (int)$wunsch['prioritaet'] === $k) ? 'selected' : '' ?>><?= h($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="w-link">Link (falls vorhanden)</label>
            <input type="url" name="wunsch_link" id="w-link" placeholder="https://..."
                   value="<?= h_legacy($wunsch['link'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="w-text">Nähere Beschreibung</label>
            <textarea name="wunsch_text" id="w-text" rows="5"><?= h_legacy($wunsch['text'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><?= h($button) ?></button>
    </form>
</div>

<?php if ($wunsch): ?>
<div class="card">
    <h3>Wunsch löschen</h3>
    <form method="post" action="index.php" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_status">
        <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
        <input type="hidden" name="status" value="geloescht">
        <input type="hidden" name="redirect" value="index.php">
        <button type="submit" class="btn btn-danger" data-confirm="Willst du diesen Wunsch wirklich löschen?">✕ Wunsch löschen</button>
    </form>
</div>
<?php endif; ?>
