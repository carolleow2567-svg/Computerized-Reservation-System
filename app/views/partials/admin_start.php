<?php
/**
 * Admin layout (sidebar + top bar), matching the team prototype.
 * Set before including:
 *   $adminActive = 'trains' | 'schedules' | 'bookings';
 *   $adminCrumb  = 'Manage Trains';   // breadcrumb label (optional)
 * Close the layout with admin_end.php.
 */
$adminActive = $adminActive ?? '';
$adminCrumb  = $adminCrumb ?? '';

$adminNav = [
    'trains'    => ['trains.php',    'Manage Trains',
        '<rect x="5" y="3" width="14" height="14" rx="3"></rect><path d="M5 11h14M8 21l2-4M16 21l-2-4"></path>'],
    'schedules' => ['schedules.php', 'Manage Schedules',
        '<rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M3 9h18M8 2v4M16 2v4"></path>'],
    'bookings'  => ['bookings.php',  'Manage Bookings',
        '<rect x="4" y="3" width="16" height="18" rx="2"></rect><path d="M8 8h8M8 12h8M8 16h5"></path>'],
];
?>
<div class="shell">

    <aside class="side">
        <a class="logo" href="trains.php">
            <svg width="32" height="32" viewBox="0 0 40 40" aria-hidden="true">
                <rect width="40" height="40" rx="8" fill="#0F766E"></rect>
                <rect x="10" y="8" width="20" height="18" rx="4" fill="#fff"></rect>
                <rect x="13" y="12" width="14" height="6" rx="1.5" fill="#0F766E"></rect>
                <circle cx="15" cy="22" r="1.6" fill="#0F766E"></circle>
                <circle cx="25" cy="22" r="1.6" fill="#0F766E"></circle>
                <path d="M12 33l4-5M28 33l-4-5" stroke="#fff" stroke-width="2" stroke-linecap="round"></path>
            </svg>
            RailMY Admin
        </a>

        <div class="sec">Operations</div>

        <nav aria-label="Admin navigation">
            <?php foreach ($adminNav as $key => [$href, $label, $icon]): ?>
                <a class="ni<?= $key === $adminActive ? ' on' : '' ?>" href="<?= $href ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><?= $icon ?></svg>
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="main">

        <header class="tb">
            <div class="mut">
                Admin / <span class="b crumb"><?= htmlspecialchars($adminCrumb, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="tb-user">
                <span class="role-tag">Role: Administrator</span>
                <span class="tb-avatar" aria-hidden="true">AD</span>
            </div>
        </header>

        <main class="pg">
