<?php if (!defined('APP_ROOT')) die();

$user = auth_require_login();
$db = Database::getInstance();

$wuensche = $db->fetchAll(
    "SELECT wuensche.*, COUNT(kommentare.id) AS num_kom
     FROM wuensche
     LEFT JOIN kommentare ON wuensche.id = kommentare.wunsch_id
     WHERE wuensche.status2 = 'locked' AND wuensche.reserviert = ?
     GROUP BY wuensche.id
     ORDER BY wuensche.name ASC, wuensche.prioritaet ASC",
    [$user['name']]
);
?>

<h2>Meine Reservierungen</h2>

<?php if (empty($wuensche)): ?>
    <div class="empty-state">
        <p>Keine Reservierungen gefunden!</p>
    </div>
<?php else: ?>
    <div class="wish-list">
        <?php foreach ($wuensche as $wunsch): ?>
        <div class="wish-card">
            <div class="wish-header">
                <span class="wish-title"><a href="index.php?page=details&id=<?= (int)$wunsch['id'] ?>&back=<?= urlencode('index.php?page=reservations') ?>" class="wish-title-link"><?= h_legacy($wunsch['titel']) ?></a><?php if (!empty(trim($wunsch['text']))): ?> <a href="index.php?page=details&id=<?= (int)$wunsch['id'] ?>&hl=desc&back=<?= urlencode('index.php?page=reservations') ?>" class="info-icon" title="Beschreibung vorhanden">ℹ️</a><?php endif; ?></span>
            </div>
            <div class="wish-meta">
                <span class="wish-owner">von <a href="index.php?page=wishes&name=<?= urlencode($wunsch['name']) ?>"><?= h($wunsch['name']) ?></a></span>
                <span class="status-badge status-<?= h($wunsch['status']) ?>"><?= $STATUS_LABELS[$wunsch['status']] ?? $wunsch['status'] ?></span>
                <a href="index.php?page=details&id=<?= (int)$wunsch['id'] ?>&back=<?= urlencode('index.php?page=reservations') ?>#kommentare" class="comment-count">💬 <?= (int)$wunsch['num_kom'] ?></a>
                <?php if (!empty($wunsch['link'])): ?>
                <span class="wish-link"><?= make_link($wunsch['link'], '🔗 Link') ?></span>
                <?php endif; ?>
            </div>
            <div class="wish-actions">
                <form method="post" action="index.php" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_reservation">
                    <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                    <input type="hidden" name="lock" value="unlock">
                    <input type="hidden" name="redirect" value="index.php?page=reservations">
                    <button type="submit" class="btn btn-small btn-warning" data-confirm="Reservierung aufheben?">🔓 Reservierung aufheben</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
