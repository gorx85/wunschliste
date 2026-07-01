<?php if (!defined('APP_ROOT')) die();

$user = auth_require_login();
$db = Database::getInstance();

$id = (int) ($_GET['id'] ?? 0);
$del = isset($_GET['del']);

$wunsch = $db->fetchOne("SELECT * FROM wuensche WHERE id = ?", [$id]);

if (!$wunsch) {
    echo '<div class="card"><p>Wunsch nicht gefunden!</p><a href="index.php" class="btn btn-primary">Zurück</a></div>';
    return;
}

$isOwner = ($wunsch['name'] === $user['name']);
$reservierung = 'nicht reserviert';
if ($wunsch['status2'] === 'locked') {
    $reservierung = ($wunsch['reserviert'] === $user['name'])
        ? 'reserviert von dir'
        : 'reserviert von ' . h($wunsch['reserviert']);
}

// Get comments (only visible to non-owners)
$kommentare = [];
if (!$isOwner) {
    $kommentare = $db->fetchAll(
        "SELECT * FROM kommentare WHERE wunsch_id = ? ORDER BY timestamp ASC",
        [$id]
    );
}
?>
<?php
$backUrl = $_GET['back'] ?? ($isOwner ? 'index.php' : 'index.php?page=wishes&name=' . urlencode($wunsch['name']));
?>

<a href="<?= h($backUrl) ?>" class="back-link">← Zurück</a>
<div class="card">
    <h2>Wunsch von <?= h($wunsch['name']) ?></h2>

    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Titel</span>
            <span class="detail-value"><?= h_legacy($wunsch['titel']) ?></span>
        </div>

        <?php if (!empty($wunsch['link'])): ?>
        <div class="detail-item">
            <span class="detail-label">Link</span>
            <span class="detail-value"><?= make_link($wunsch['link'], 'Zum Link →') ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($wunsch['text'])): ?>
        <div class="detail-item detail-beschreibung">
            <span class="detail-label">Beschreibung</span>
            <span class="detail-value"><?= linkify_text($wunsch['text']) ?></span>
        </div>
        <?php endif; ?>

        <div class="detail-item">
            <span class="detail-label">Letzte Änderung</span>
            <span class="detail-value"><?= format_date((int)$wunsch['timestamp']) ?></span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="detail-value">
                <span class="status-badge status-<?= h($wunsch['status']) ?>"><?= $STATUS_LABELS[$wunsch['status']] ?? $wunsch['status'] ?></span>
            </span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Priorität</span>
            <span class="detail-value"><?= h($PRIORITIES[(int)$wunsch['prioritaet']] ?? '') ?></span>
        </div>

        <?php if (!$isOwner): ?>
        <div class="detail-item">
            <span class="detail-label">Reservierung</span>
            <span class="detail-value"><?= $reservierung ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="detail-actions">
        <?php if ($isOwner): ?>
            <a href="index.php?page=new_wish&id=<?= (int)$wunsch['id'] ?>&back=<?= urlencode('index.php?page=details&id=' . (int)$wunsch['id'] . (isset($_GET['back']) ? '&back=' . urlencode($_GET['back']) : '')) ?>" class="btn btn-primary">Bearbeiten</a>
        <?php else: ?>
            <?php if ($wunsch['status2'] === 'unlocked'): ?>
            <form method="post" action="index.php" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_reservation">
                <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                <input type="hidden" name="lock" value="lock">
                <input type="hidden" name="redirect" value="index.php?page=details&id=<?= (int)$wunsch['id'] ?>">
                <button type="submit" class="btn btn-success" data-confirm="Wunsch reservieren?">🔒 Reservieren</button>
            </form>
            <?php elseif ($wunsch['reserviert'] === $user['name']): ?>
            <form method="post" action="index.php" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_reservation">
                <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                <input type="hidden" name="lock" value="unlock">
                <input type="hidden" name="redirect" value="index.php?page=details&id=<?= (int)$wunsch['id'] ?>">
                <button type="submit" class="btn btn-warning" data-confirm="Reservierung aufheben?">🔓 Reservierung aufheben</button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div>

<?php if (!$isOwner): ?>
<div class="card" id="kommentare">
    <h3>Kommentare</h3>

    <?php if (!empty($kommentare)): ?>
    <div class="comment-list">
        <?php foreach ($kommentare as $k): ?>
        <div class="comment">
            <div class="comment-header">
                <strong><?= h($k['name']) ?></strong>
                <span class="comment-date"><?= format_date((int)$k['timestamp'], true) ?></span>
            </div>
            <div class="comment-body"><?= linkify_text($k['text']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-muted">Noch keine Kommentare.</p>
    <?php endif; ?>

    <form method="post" action="index.php" class="comment-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_comment">
        <input type="hidden" name="kom_id" value="<?= (int)$wunsch['id'] ?>">

        <div class="form-group">
            <label for="kom-text">Neuer Kommentar</label>
            <textarea name="kom_text" id="kom-text" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Kommentar speichern</button>
    </form>
</div>
<?php endif; ?>

<?php if (isset($_GET['hl']) && $_GET['hl'] === 'desc'): ?>
<script>
(function() {
    var el = document.querySelector('.detail-beschreibung');
    if (el) el.classList.add('highlight');
})();
</script>
<?php endif; ?>
