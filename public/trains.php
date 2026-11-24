<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/TrainController.php';

$controller = new TrainController($pdo);
// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$trains = $controller->index();

// Extra read-only details for the list (route, times, classes).
// If these tables are missing the page still works and shows dashes.
try {
    $details           = $controller->listDetails();
    $scheduleSummaries = $details['schedules'];
    $classSummaries    = $details['classes'];
} catch (PDOException $e) {
    error_log($e->getMessage());
    $scheduleSummaries = [];
    $classSummaries    = [];
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Trains | CRS Admin</title>

    <link rel="stylesheet" href="assets/css/trains.css?v=<?= (int) @filemtime(__DIR__ . '/assets/css/trains.css') ?>">
</head>

<body>

<?php
$adminActive = 'trains';
$adminCrumb  = 'Manage Trains';
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
            <h1>Manage trains</h1>
            <p><?= count($trains) ?> train record(s) &middot; <span id="trainShown"><?= count($trains) ?></span> matching filters</p>
        </div>

        <a href="train-create.php" class="btn-primary">
            + Add New Train
        </a>
    </header>
    <?php if (($_GET['success'] ?? '') === 'created'): ?>

    <div class="success-message">
        Train record added successfully!
    </div>

<?php endif; ?>
    <section class="table-section" aria-label="Search and filters">
        <div class="toolbar">
            <div class="grow">
                <label for="trainSearch">Search</label>
                <input id="trainSearch" type="search" placeholder="Train number, name or station" autocomplete="off">
            </div>
            <div>
                <label for="trainStatus">Status</label>
                <select id="trainStatus">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <label for="trainClass">Class</label>
                <select id="trainClass">
                    <option value="">All</option>
                    <option value="Platinum">Platinum</option>
                    <option value="Gold">Gold</option>
                    <option value="Silver">Silver</option>
                </select>
            </div>
            <button type="button" class="btn-secondary" id="trainClear">Clear</button>
        </div>
    </section>

    <section class="table-section card-flush" aria-label="Train records">

        <div class="table-heading">
            <h2>Train Records</h2>
            <span>Total Trains: <?= count($trains) ?></span>
        </div>

        <div class="table-wrapper">

            <table id="trainTable">

                <thead>
                    <tr>
                        <th>Train No.</th>
                        <th>Train Name</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Departs</th>
                        <th>Arrives</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($trains)): ?>

                    <tr>
                        <td colspan="9" class="empty">
                            No train records found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($trains as $train): ?>

                        <?php
                        $tid      = (int) $train['train_id'];
                        $sched    = $scheduleSummaries[$tid] ?? null;
                        $classes  = $classSummaries[$tid] ?? '';
                        $classTxt = $classes !== '' ? $classes : $train['train_type'];
                        ?>

                        <tr data-status="<?= e($train['status']) ?>"
                            data-classes="<?= e($classTxt) ?>">

                            <td class="b"><?= e($train['train_number']) ?></td>

                            <td><?= e($train['train_name']) ?></td>

                            <td><?= $sched ? e($sched['origin_name']) : '<span class="mut">&mdash;</span>' ?></td>

                            <td><?= $sched ? e($sched['destination_name']) : '<span class="mut">&mdash;</span>' ?></td>

                            <td>
                                <?php if ($sched): ?>
                                    <?= e(date('H:i', strtotime($sched['departure_datetime']))) ?>
                                    <br><small><?= e(date('d M Y', strtotime($sched['departure_datetime']))) ?></small>
                                <?php else: ?>
                                    <span class="mut">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($sched): ?>
                                    <?= e(date('H:i', strtotime($sched['arrival_datetime']))) ?>
                                    <br><small><?= e(date('d M Y', strtotime($sched['arrival_datetime']))) ?></small>
                                <?php else: ?>
                                    <span class="mut">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <td><?= e($classTxt) ?></td>

                            <td>
                                <span class="status <?= e($train['status']) ?>">
                                    <?= e(ucfirst($train['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <div class="row-actions">

                                    <button
                                        type="button"
                                        class="btn-view"
                                        aria-label="View <?= e($train['train_number']) ?>"
                                        onclick="document.getElementById('train-dialog-<?= $tid ?>').showModal()">
                                        View
                                    </button>

                                    <a
                                        href="train-edit.php?id=<?= e($train['train_id']) ?>"
                                        class="btn-edit"
                                        aria-label="Edit <?= e($train['train_number']) ?>">
                                        Edit
                                    </a>

                                    <?php if ($train['status'] === 'active'): ?>
                                        <form method="POST"
                                              action="train-deactivate.php"
                                              style="display:inline;"
                                              onsubmit="return confirm('Are you sure you want to deactivate this train?');">

                                            <input type="hidden" name="train_id"value="<?= e($train['train_id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                            <button type="submit" class="btn-deactivate">Deactivate</button>
                                        </form>

                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="empty" id="trainNoMatch" hidden>
            <div class="b">No trains found</div>
            Adjust the search or filters, or add a new train.
        </div>

    </section>

<?php foreach ($trains as $train): ?>

    <?php
    $tid      = (int) $train['train_id'];
    $sched    = $scheduleSummaries[$tid] ?? null;
    $classes  = $classSummaries[$tid] ?? '';
    $classTxt = $classes !== '' ? $classes : $train['train_type'];
    ?>

    <dialog id="train-dialog-<?= $tid ?>" class="booking-dialog train-dialog">

        <div class="booking-dialog-header">
            <h2><?= e($train['train_number']) ?> &middot; <?= e($train['train_name']) ?></h2>
            <span class="status <?= e($train['status']) ?>">
                <?= e(ucfirst($train['status'])) ?>
            </span>
        </div>

        <dl class="dl">
            <dt>Train type</dt>
            <dd><?= e($train['train_type']) ?></dd>

            <dt>Route</dt>
            <dd>
                <?= $sched
                    ? e($sched['origin_name']) . ' &rarr; ' . e($sched['destination_name'])
                    : 'No schedule yet' ?>
            </dd>

            <dt>Departure</dt>
            <dd><?= $sched ? e(date('d M Y, H:i', strtotime($sched['departure_datetime']))) : '&mdash;' ?></dd>

            <dt>Arrival</dt>
            <dd><?= $sched ? e(date('d M Y, H:i', strtotime($sched['arrival_datetime']))) : '&mdash;' ?></dd>

            <dt>Classes</dt>
            <dd><?= e($classTxt) ?></dd>

            <dt>Schedules</dt>
            <dd>Departure times are managed in Manage Schedules.</dd>
        </dl>

        <div class="booking-dialog-actions">
            <button type="button" class="btn-close" onclick="this.closest('dialog').close()">Close</button>
            <a href="train-edit.php?id=<?= e($train['train_id']) ?>" class="btn-primary">Edit Train</a>
        </div>

    </dialog>

<?php endforeach; ?>

<script>
// Display-only search/filter over the rows already rendered by PHP.
(function () {
    var q = document.getElementById('trainSearch');
    var st = document.getElementById('trainStatus');
    var cls = document.getElementById('trainClass');
    var clear = document.getElementById('trainClear');
    var shown = document.getElementById('trainShown');
    var none = document.getElementById('trainNoMatch');
    var rows = document.querySelectorAll('#trainTable tbody tr[data-status]');
    if (!q || !rows.length) { return; }

    function apply() {
        var term = q.value.trim().toLowerCase();
        var status = st.value;
        var count = 0;
        rows.forEach(function (row) {
            var ok = (!status || row.dataset.status === status) &&
                     (!cls.value || row.dataset.classes.indexOf(cls.value) !== -1) &&
                     (!term || row.textContent.toLowerCase().indexOf(term) !== -1);
            row.hidden = !ok;
            if (ok) { count++; }
        });
        shown.textContent = count;
        none.hidden = count !== 0;
    }

    q.addEventListener('input', apply);
    st.addEventListener('change', apply);
    cls.addEventListener('change', apply);
    clear.addEventListener('click', function () { q.value = ''; st.value = ''; cls.value = ''; apply(); });
})();
</script>
<?php require __DIR__ . '/../app/views/partials/admin_end.php'; ?>

</body>
</html>