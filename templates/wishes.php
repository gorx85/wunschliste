<?php if (!defined('APP_ROOT')) die();

$db = Database::getInstance();
$viewName = $_GET['name'] ?? $user['name'];
$isOwnList = ($viewName === $user['name']);
$backParam = urlencode($isOwnList ? 'index.php' : 'index.php?page=wishes&name=' . urlencode($viewName));

// Build query constraints
if ($isOwnList) {
    // Own list: hide deleted
    $wuensche = $db->fetchAll(
        "SELECT wuensche.*, COUNT(kommentare.id) AS num_kom
         FROM wuensche
         LEFT JOIN kommentare ON wuensche.id = kommentare.wunsch_id
         WHERE wuensche.name = ? AND wuensche.status != 'geloescht'
         GROUP BY wuensche.id
         ORDER BY wuensche.status ASC, wuensche.prioritaet ASC, wuensche.timestamp DESC",
        [$viewName]
    );
} else {
    // Other's list: hide deleted unless reserved or has comments
    $wuensche = $db->fetchAll(
        "SELECT wuensche.*, COUNT(kommentare.id) AS num_kom
         FROM wuensche
         LEFT JOIN kommentare ON wuensche.id = kommentare.wunsch_id
         WHERE wuensche.name = ?
           AND (wuensche.status != 'geloescht' OR wuensche.status2 = 'locked' OR kommentare.id IS NOT NULL)
         GROUP BY wuensche.id
         ORDER BY wuensche.status ASC, wuensche.prioritaet ASC, wuensche.timestamp DESC",
        [$viewName]
    );
}

$titel = $isOwnList ? 'Meine Wünsche' : 'Wünsche von ' . h($viewName);
?>

<h2><?= $titel ?></h2>

<?php if (empty($wuensche)): ?>
    <div class="empty-state">
        <p>Keine Wünsche gefunden!</p>
        <?php if ($isOwnList): ?>
        <a href="index.php?page=new_wish" class="btn btn-primary">Ersten Wunsch erstellen</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="wish-list">
        <?php foreach ($wuensche as $wunsch): ?>
        <div class="wish-card">
            <div class="wish-header">
                <span class="wish-title"><a href="index.php?page=details&id=<?= (int)$wunsch['id'] ?>&back=<?= $backParam ?>" class="wish-title-link"><?= h_legacy($wunsch['titel']) ?></a><?php if (!$isOwnList && !empty(trim($wunsch['text']))): ?> <a href="index.php?page=details&id=<?= (int)$wunsch['id'] ?>&hl=desc&back=<?= $backParam ?>" class="info-icon" title="Beschreibung vorhanden">ℹ️</a><?php endif; ?></span>
                <span class="wish-priority" title="Priorität: <?= $PRIORITIES[$wunsch['prioritaet']] ?? '' ?>">P<?= (int)$wunsch['prioritaet'] ?></span>
            </div>
            <div class="wish-meta">
                <span class="status-badge status-<?= h($wunsch['status']) ?>"><?= $STATUS_LABELS[$wunsch['status']] ?? $wunsch['status'] ?></span>
                <?php if (!$isOwnList && $wunsch['status2'] === 'locked'): ?>
                <span class="status-badge status-locked">🔒 <?= $wunsch['reserviert'] === $user['name'] ? 'reserviert von dir' : 'reserviert von ' . h($wunsch['reserviert']) ?></span>
                <?php endif; ?>
                <?php if (!$isOwnList): ?>
                <a href="index.php?page=details&id=<?= (int)$wunsch['id'] ?>&back=<?= $backParam ?>#kommentare" class="comment-count">💬 <?= (int)$wunsch['num_kom'] ?></a>
                <?php endif; ?>
                <?php if (!empty($wunsch['link'])): ?>
                <span class="wish-link"><?= make_link($wunsch['link'], '🔗 Link') ?></span>
                <?php endif; ?>
            </div>

            <?php if ($isOwnList): ?>
            <div class="wish-actions">
                <a href="index.php?page=new_wish&id=<?= (int)$wunsch['id'] ?>&back=<?= $backParam ?>" class="btn btn-small">Bearbeiten</a>

                <?php if ($wunsch['status'] !== 'gewuenscht'): ?>
                <form method="post" action="index.php" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                    <input type="hidden" name="status" value="gewuenscht">
                    <input type="hidden" name="redirect" value="index.php">
                    <button type="submit" class="btn btn-small" data-confirm="Status zurücksetzen?">↩ Doch nicht erhalten</button>
                </form>
                <?php endif; ?>

                <?php if ($wunsch['status'] !== 'erhalten'): ?>
                <form method="post" action="index.php" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                    <input type="hidden" name="status" value="erhalten">
                    <input type="hidden" name="redirect" value="index.php">
                    <button type="submit" class="btn btn-small btn-success" data-confirm="Wunsch als erhalten markieren?">✓ Erhalten</button>
                </form>
                <?php endif; ?>
            </div>

            <?php else: ?>
                <?php if ($wunsch['status2'] === 'unlocked'): ?>
                <div class="wish-actions">
                    <form method="post" action="index.php" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_reservation">
                        <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                        <input type="hidden" name="lock" value="lock">
                        <input type="hidden" name="redirect" value="index.php?page=wishes&name=<?= urlencode($viewName) ?>">
                        <button type="submit" class="btn btn-small btn-success" data-confirm="Wunsch reservieren?">🔒 Reservieren</button>
                    </form>
                </div>
                <?php elseif ($wunsch['reserviert'] === $user['name']): ?>
                <div class="wish-actions">
                    <form method="post" action="index.php" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_reservation">
                        <input type="hidden" name="wish_id" value="<?= (int)$wunsch['id'] ?>">
                        <input type="hidden" name="lock" value="unlock">
                        <input type="hidden" name="redirect" value="index.php?page=wishes&name=<?= urlencode($viewName) ?>">
                        <button type="submit" class="btn btn-small btn-warning" data-confirm="Reservierung aufheben?">🔓 Reservierung aufheben</button>
                    </form>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
