<?php if (!defined('APP_ROOT')) die(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wunschliste</title>
    <link rel="stylesheet" href="style.css?v=2">
    <meta name="theme-color" content="#4a4aaa">
</head>
<body class="<?= $user ? 'has-sidebar' : '' ?>">
    <?php if ($user): ?>
    <header class="app-header">
        <button class="menu-toggle" id="menuToggle" aria-label="Menü öffnen">&#9776;</button>
        <h1 class="app-title">Wunschliste</h1>
        <div class="user-menu" id="userMenu">
            <span class="user-badge" id="userBadge"><?= h($user['name']) ?> ▾</span>
            <div class="user-dropdown" id="userDropdown">
                <ul class="dropdown-list">
                    <li><a href="index.php?page=password" class="dropdown-item">🔑 Passwort ändern</a></li>
                    <li>
                        <form method="post" action="index.php" class="dropdown-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="dropdown-item">🚪 Ausloggen</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-title sidebar-title-mobile">Navigation</span>
            <span class="sidebar-title sidebar-title-desktop">Wunschliste</span>
            <button class="sidebar-close" id="sidebarClose" aria-label="Menü schließen">&times;</button>
            <div class="user-menu user-menu-sidebar" id="userMenuSidebar">
                <span class="user-badge" id="userBadgeSidebar"><?= h($user['name']) ?> ▾</span>
                <div class="user-dropdown" id="userDropdownSidebar">
                    <ul class="dropdown-list">
                        <li><a href="index.php?page=password" class="dropdown-item">🔑 Passwort ändern</a></li>
                        <li>
                            <form method="post" action="index.php" class="dropdown-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="dropdown-item">🚪 Ausloggen</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <ul class="nav-list">
            <li><a href="index.php" class="nav-link <?= $page === 'wishes' && !isset($_GET['name']) ? 'active' : '' ?>">📝 Meine Wünsche</a></li>
            <li><a href="index.php?page=new_wish" class="nav-link <?= $page === 'new_wish' ? 'active' : '' ?>">➕ Neuer Wunsch</a></li>
            <li><a href="index.php?page=reservations" class="nav-link <?= $page === 'reservations' ? 'active' : '' ?>">🔒 Reservierungen</a></li>

            <?php if (!empty($otherUsers)): ?>
            <li class="nav-separator"></li>
            <?php foreach ($otherUsers as $u): ?>
            <li><a href="index.php?page=wishes&name=<?= urlencode($u['name']) ?>" class="nav-link <?= ($page === 'wishes' && ($_GET['name'] ?? '') === $u['name']) ? 'active' : '' ?>">👤 <?= h($u['name']) ?></a></li>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php else: ?>
    <header class="app-header">
        <h1 class="app-title">Wunschliste</h1>
    </header>
    <?php endif; ?>

    <main class="content">
        <?php if ($flashMessage): ?>
        <div class="flash-message"><?= h($flashMessage) ?></div>
        <?php endif; ?>

        <?php
        // Route to the correct page template
        switch ($page) {
            case 'login':
                include 'templates/login.php';
                break;
            case 'register':
                include 'templates/register.php';
                break;
            case 'wishes':
                include 'templates/wishes.php';
                break;
            case 'new_wish':
                include 'templates/new_wish.php';
                break;
            case 'details':
                include 'templates/details.php';
                break;
            case 'reservations':
                include 'templates/reservations.php';
                break;
            case 'password':
                include 'templates/password.php';
                break;
            default:
                include 'templates/wishes.php';
                break;
        }
        ?>
    </main>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <p class="modal-message" id="modalMessage"></p>
            <div class="modal-actions">
                <button class="btn" id="modalCancel">Abbrechen</button>
                <button class="btn btn-primary" id="modalConfirm">OK</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const toggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const close = document.getElementById('sidebarClose');

        function openMenu() {
            sidebar.classList.add('open');
            overlay.classList.add('visible');
            document.body.classList.add('no-scroll');
        }
        function closeMenu() {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
            document.body.classList.remove('no-scroll');
        }

        if (toggle) toggle.addEventListener('click', openMenu);
        if (overlay) overlay.addEventListener('click', closeMenu);
        if (close) close.addEventListener('click', closeMenu);

        // Close on nav link click (mobile)
        document.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', closeMenu);
        });

        // User dropdowns
        function setupDropdown(badgeId, dropdownId) {
            var badge = document.getElementById(badgeId);
            var dropdown = document.getElementById(dropdownId);
            if (badge && dropdown) {
                badge.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('open');
                });
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && e.target !== badge) {
                        dropdown.classList.remove('open');
                    }
                });
            }
        }
        setupDropdown('userBadge', 'userDropdown');
        setupDropdown('userBadgeSidebar', 'userDropdownSidebar');

        // Modal confirmation
        var modalOverlay = document.getElementById('modalOverlay');
        var modalMessage = document.getElementById('modalMessage');
        var modalConfirm = document.getElementById('modalConfirm');
        var modalCancel = document.getElementById('modalCancel');
        var pendingForm = null;

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-confirm]');
            if (btn) {
                e.preventDefault();
                pendingForm = btn.closest('form');
                modalMessage.textContent = btn.getAttribute('data-confirm');
                modalOverlay.classList.add('visible');
            }
        });

        if (modalConfirm) {
            modalConfirm.addEventListener('click', function() {
                modalOverlay.classList.remove('visible');
                if (pendingForm) pendingForm.submit();
                pendingForm = null;
            });
        }

        if (modalCancel) {
            modalCancel.addEventListener('click', function() {
                modalOverlay.classList.remove('visible');
                pendingForm = null;
            });
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === modalOverlay) {
                    modalOverlay.classList.remove('visible');
                    pendingForm = null;
                }
            });
        }
    })();
    </script>
</body>
</html>
