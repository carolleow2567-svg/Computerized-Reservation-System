<?php

session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/ScheduleController.php';

$controller = new ScheduleController($pdo);
$schedules = $controller->index();

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');

$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['departure_date'] ?? '';

$allowedStatuses = ['pending', 'confirmed', 'cancelled'];

// Validate filter values.
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

// Accept only a valid YYYY-MM-DD date.
if (
    $dateFilter !== '' &&
    (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter) ||
        !DateTime::createFromFormat('!Y-m-d', $dateFilter)
    )
) {
    $dateFilter = '';
}

// Apply search and all filters together.
$schedules = array_filter($schedules, function ($schedule) use (
    $search,
    $statusFilter,
    $dateFilter
) {
    $matchesSearch = true;
    $matchesStatus = true;
    $matchesDate = true;

    if ($search !== '') {
        $matchesSearch =
            stripos($schedules['train_number'] ?? '', $search) !== false ||
            stripos($schedules['train_name'] ?? '', $search) !== false ||
            stripos($schedules['origin'] ?? '', $search) !== false ||
            stripos($schedules['destination'] ?? '', $search) !== false;
    }

    if ($statusFilter !== '') {
        $matchesStatus =
            $schedules['status'] === $statusFilter;
    }

    if ($dateFilter !== '') {
        $matchesDate =
            date('Y-m-d', strtotime($schedules['departure_datetime'])) === $dateFilter;
    }

    return $matchesSearch &&
           $matchesStatus &&
           $matchesDate;
});

$totalSchedules = count($schedules);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/m7.css?v=<?= (int) @filemtime(__DIR__ . '/assets/css/m7.css') ?>">
</head>

<body>

<?php
$adminActive = 'schedules';
$adminCrumb  = 'Manage Schedules';
require __DIR__ . '/../app/views/partials/admin_start.php';
?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message">
            <?= e($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message">
            <?= e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <header class="page-header">
        <div>
            <h1>Manage Train Schedules</h1>
            <p>View and manage train departure schedules.</p>
        </div>

        <a href="schedule-create.php" class="btn-primary">
            + Add Schedule
        </a>
    </header>

    <section class="table-section" aria-label="Search and filters">
        <div class="toolbar">
            <div class="grow">
                <label for="fSearch">Search</label>
                <input id="fSearch" type="search" autocomplete="off"
                    placeholder="Train, origin or destination">
            </div>
            <div>
                <label for="fStatus">Status</label>
                <select id="fStatus">
                    <option value="">All</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="delayed">Delayed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div>
                <label for="fDate">Departure date</label>
                <input id="fDate" type="date">
            </div>
            <button type="button" class="btn-secondary" id="fClear">Clear</button>
        </div>
        <div class="filter-count"><span id="fShown"><?= count($schedules) ?></span> schedule(s) found</div>
    </section>

    <section class="table-section">

        <div class="table-heading">
            <h2>Schedule Records</h2>
            <span>Total Schedules: <?= count($schedules) ?></span>
        </div>

        <div class="table-wrapper">
            <table id="recordTable">
                <thead>
                    <tr>
                        <th>Schedule ID</th>
                        <th>Train</th>
                        <th>Route</th>
                        <th data-sortable>Departs</th>
                        <th>Arrives</th>
                        <th>Fare (RM)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="8" class="empty">
                            No schedule records found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($schedules as $schedule): ?>


                        <tr data-status="<?= e($schedule['status']) ?>"
                            data-date="<?= e(date('Y-m-d', strtotime($schedule['departure_datetime']))) ?>">
                            
                            <td><?= e($schedule['schedule_id']) ?></td>

                            <td>
                                <?= e($schedule['train_number']) ?><br>
                                <?= e($schedule['train_name']) ?>
                            </td>

                            <td>
                                <?= e($schedule['origin_name']) ?>
                                <br>&nbsp;→
                                <?= e($schedule['destination_name']) ?>
                            </td>
                            
                            <td data-value="<?= $schedule ? e($schedule['departure_datetime']) : '' ?>">
                                <?php if ($schedule): ?>
                                    <?= e(date('H:i', strtotime($schedule['departure_datetime']))) ?>
                                    <br><small><?= e(date('d M Y', strtotime($schedule['departure_datetime']))) ?></small>
                                <?php else: ?>
                                    <span class="mut">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if ($schedule): ?>
                                    <?= e(date('H:i', strtotime($schedule['arrival_datetime']))) ?>
                                    <br><small><?= e(date('d M Y', strtotime($schedule['arrival_datetime']))) ?></small>
                                <?php else: ?>
                                    <span class="mut">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <td><?= e(number_format((float)$schedule['base_fare'], 2)) ?></td>
                            
                            <td><span class="schedule-status <?= e($schedule['status']) ?>">
                                <?= e(ucfirst($schedule['status'])) ?></span>
                            </td>
                            
                            <td><a href="schedule-edit.php?id=<?= e($schedule['schedule_id']) ?>"
                                    class="btn-edit">Edit</a>
                                    
                                <?php if (in_array($schedule['status'], ['scheduled', 'delayed'], true)): ?>
                                        
                                    <form method="POST"
                                          action="schedule-cancel.php"
                                          style="display:inline;"
                                          onsubmit="return confirm('Cancel this schedule?');">
                                              
                                        <input type="hidden" name="schedule_id" value="<?= e($schedule['schedule_id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn-deactivate">
                                            Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

<script>
// Click a header marked data-sortable to sort the table by that column.
(function () {
    document.querySelectorAll('th[data-sortable]').forEach(function (th) {
        th.setAttribute('data-sort', 'none');
        th.tabIndex = 0;

        function sort() {
            var table = th.closest('table');
            var tbody = table.tBodies[0];
            var col = th.cellIndex;
            var dir = th.getAttribute('data-sort') === 'asc' ? 'desc' : 'asc';

            table.querySelectorAll('th[data-sortable]').forEach(function (h) {
                h.setAttribute('data-sort', 'none');
            });
            th.setAttribute('data-sort', dir);

            var rows = Array.prototype.slice.call(tbody.rows).filter(function (r) {
                return r.cells.length > col;      // skip the "no records" row
            });

            function val(row) {
                var c = row.cells[col];
                return (c.getAttribute('data-value') || c.textContent).trim();
            }

            rows.sort(function (a, b) {
                var r = val(a).localeCompare(val(b), undefined, {numeric: true, sensitivity: 'base'});
                return dir === 'asc' ? r : -r;
            });

            rows.forEach(function (r) { tbody.appendChild(r); });
        }

        th.addEventListener('click', sort);
        th.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); sort(); }
        });
    });
})();
</script>

<?php require __DIR__ . '/../app/views/partials/admin_end.php'; ?>

</body>
</html>