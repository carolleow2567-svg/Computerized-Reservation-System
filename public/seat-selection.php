<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/m2_helpers.php';
require_once __DIR__ . '/../app/controllers/SeatController.php';

$userId = require_passenger($pdo);
$ctl = new SeatController($pdo);
extract($ctl->seatPage($_GET, $userId)); // q, errors, stations, fatal, flashError, seg, classFares, coaches

$pageTitle = 'Choose your seats';
$step = 2;
$searchUrl = 'train-search.php?' . http_build_query(array_filter([
    'from' => $q['from'], 'to' => $q['to'], 'date' => $q['date'], 'n' => $q['n'],
]));

require __DIR__ . '/../app/views/partials/m2_header.php';
?>
  <div class="row sp">
    <h1 class="h1">Choose your seats</h1>
    <a class="btn ghost" href="<?= e($searchUrl) ?>">&larr; Change train</a>
  </div>

<?php if ($fatal): ?>
  <div class="alert bad" role="alert"><?= e($fatal) ?></div>
  <p><a class="btn gold" href="train-search.php">Back to search</a></p>
<?php else:
    $firstCoach = array_key_first($coaches);
    foreach ($coaches as $code => $c) { if ($c['free'] > 0) { $firstCoach = $code; break; } }
    $classLink = fn(string $cls) => 'seat-selection.php?' . http_build_query([
        'schedule_id' => $q['schedule_id'], 'date' => $q['date'], 'from' => $q['from'],
        'to' => $q['to'], 'n' => $q['n'], 'class' => $cls,
    ]);
?>
  <form id="seatform" method="post" action="seat-hold.php" data-max="<?= (int) $q['n'] ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="schedule_id" value="<?= (int) $q['schedule_id'] ?>">
    <input type="hidden" name="date" value="<?= e($q['date']) ?>">
    <input type="hidden" name="from" value="<?= (int) $q['from'] ?>">
    <input type="hidden" name="to" value="<?= (int) $q['to'] ?>">
    <input type="hidden" name="n" value="<?= (int) $q['n'] ?>">
    <input type="hidden" name="class" value="<?= e($q['class']) ?>">

    <div class="cols">
      <section class="card mapc" aria-label="Seat map">
        <div class="row sp">
          <div>
            <div class="b" style="font-size:18px"><?= e($seg['train_no']) ?> &middot; <?= e($seg['name']) ?></div>
            <div class="mut sm"><?= e(date('D, j M Y', strtotime($q['date']))) ?> &middot; departs <?= e(substr($seg['dep_time'], 0, 5)) ?>, arrives <?= e(substr($seg['arr_time'], 0, 5)) ?></div>
          </div>
          <span class="tag warn"><span id="picked">0</span> of <?= (int) $q['n'] ?> selected</span>
        </div>

        <div class="alert info sm" style="margin-top:12px">
          Availability is checked for your journey <b><?= e($stations[$q['from']]) ?> to <?= e($stations[$q['to']]) ?></b>.
          A seat booked on another part of the route can still be free for your trip.
        </div>
        <?php if ($flashError): ?><div class="alert bad" role="alert" style="margin-top:12px"><?= e($flashError) ?></div><?php endif; ?>

        <div style="margin-top:16px"><span class="b sm">Class</span>
          <div class="row" style="gap:8px;margin-top:6px">
            <?php foreach ($classFares as $cls => $fare): ?>
              <a class="chip-link <?= $cls === $q['class'] ? 'on' : '' ?>" href="<?= e($classLink($cls)) ?>" <?= $cls === $q['class'] ? 'aria-current="true"' : '' ?>><?= e($cls) ?> &middot; <?= e(money($fare)) ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (!$coaches): ?>
          <div class="alert" style="margin-top:14px">No coaches are configured for this class on this train.</div>
        <?php else: ?>
        <div style="margin-top:12px"><span class="b sm">Coach</span>
          <div class="row" style="gap:8px;margin-top:6px">
            <?php foreach ($coaches as $code => $c): ?>
              <button type="button" class="chip-link <?= $code === $firstCoach ? 'on' : '' ?>" data-coach-tab="<?= e($code) ?>" aria-pressed="<?= $code === $firstCoach ? 'true' : 'false' ?>"
                      aria-label="Coach <?= e($code) ?>, <?= (int) $c['free'] ?> seats free for your journey">Coach <?= e($code) ?> <span class="mut sm" style="margin-left:6px"><?= (int) $c['free'] ?> free</span></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="lg" style="margin-top:16px" aria-label="Seat availability legend">
          <span class="row" style="gap:8px"><span class="face">A</span>Available</span>
          <span class="row" style="gap:8px"><span class="face sel">S</span>Selected</span>
          <span class="row" style="gap:8px"><span class="face oc">X</span>Occupied</span>
          <span class="row" style="gap:8px"><span class="face un">U</span>Unavailable</span>
        </div>
        <div id="warn" class="alert" role="alert" style="margin-top:14px" hidden>You can only select <?= (int) $q['n'] ?> seat(s) for this booking. Deselect a seat to choose another.</div>

        <?php foreach ($coaches as $code => $c): ?>
          <div class="coach" role="group" aria-label="Coach <?= e($code) ?> seat layout" data-coach-panel="<?= e($code) ?>" <?= $code === $firstCoach ? '' : 'hidden' ?>>
            <div class="sm b mut" style="text-align:center;margin-bottom:10px">&larr; Rear of train &nbsp;|&nbsp; Coach <?= e($code) ?> &nbsp;|&nbsp; Front &rarr;</div>
            <div class="sr" aria-hidden="true"><span class="aisle" style="width:46px">A</span><span class="aisle" style="width:46px">B</span><span class="aisle"></span><span class="aisle" style="width:46px">C</span><span class="aisle" style="width:46px">D</span></div>
            <?php for ($r = 1; $r <= $c['rows']; $r++): ?>
              <div class="sr">
                <?php foreach (['A', 'B', 'AISLE', 'C', 'D'] as $L):
                    if ($L === 'AISLE') { echo '<span class="aisle">' . $r . '</span>'; continue; }
                    $no = $r . $L;
                    if (!isset($c['seats'][$no])) { echo '<span style="width:46px"></span>'; continue; }
                    $s = $c['seats'][$no];
                    $stateText = ['av' => 'available', 'sel' => 'selected', 'oc' => 'occupied for your journey', 'un' => 'unavailable'][$s['state']];
                    $disabled = in_array($s['state'], ['oc', 'un'], true);
                ?>
                  <label class="seat">
                    <input type="checkbox" name="seats[]" value="<?= (int) $s['id'] ?>" data-label="<?= e($code . '-' . $no) ?>"
                           aria-label="Coach <?= e($code) ?> seat <?= e($no) ?>, <?= e($stateText) ?>"
                           <?= $disabled ? 'disabled' : '' ?> <?= $s['state'] === 'sel' ? 'checked' : '' ?>>
                    <span class="face <?= $s['state'] === 'oc' ? 'oc' : ($s['state'] === 'un' ? 'un' : '') ?>"><?= e($no) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endfor; ?>
          </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <aside class="card sidec sum" aria-label="Booking summary">
        <h2 class="h2">Your selection</h2>
        <dl>
          <dt>Train</dt><dd><?= e($seg['train_no']) ?></dd>
          <dt>Date</dt><dd><?= e(date('j M Y', strtotime($q['date']))) ?></dd>
          <dt>Journey</dt><dd><?= e($stations[$q['from']]) ?> to <?= e($stations[$q['to']]) ?></dd>
          <dt>Class</dt><dd><?= e($q['class']) ?></dd>
          <dt>Passengers</dt><dd><?= (int) $q['n'] ?></dd>
          <dt>Seats</dt><dd id="picked_list">None yet</dd>
        </dl>
        <p class="mut sm">Seats are held for <?= (int) HOLD_MINUTES ?> minutes after you continue. The final fare is calculated at checkout.</p>
        <button class="btn gold wide" id="cont" type="submit" disabled>Continue to passenger details</button>
      </aside>
    </div>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/../app/views/partials/m2_footer.php'; ?>
