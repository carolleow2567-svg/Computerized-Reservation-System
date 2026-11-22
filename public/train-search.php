<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/m2_helpers.php';
require_once __DIR__ . '/../app/controllers/SeatController.php';

$userId = require_passenger($pdo);
$ctl = new SeatController($pdo);
extract($ctl->searchPage($_GET, $userId)); // submitted, q, errors, stations, classes, stationName, maxFare, sort, results, dbError

$pageTitle = 'Search trains';
$step = 1;
require __DIR__ . '/../app/views/partials/m2_header.php';
?>
  <h1 class="h1">Search trains</h1>

  <section class="card" aria-label="Search form">
    <form id="sf" method="get" action="train-search.php" novalidate>
      <div class="sf">
        <div>
          <label class="lbl" for="from">Departure station</label>
          <select id="from" name="from" class="in" required>
            <option value="">Select station</option>
            <?php foreach ($stations as $s): ?>
              <option value="<?= (int) $s['station_id'] ?>" <?= $q['from'] == $s['station_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['from'])): ?><div class="err" role="alert"><?= e($errors['from']) ?></div><?php endif; ?>
        </div>
        <div style="flex:0"><button class="swap" id="swap" type="button" aria-label="Swap departure and arrival stations">&#8646;</button></div>
        <div>
          <label class="lbl" for="to">Arrival station</label>
          <select id="to" name="to" class="in" required>
            <option value="">Select station</option>
            <?php foreach ($stations as $s): ?>
              <option value="<?= (int) $s['station_id'] ?>" <?= $q['to'] == $s['station_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['to'])): ?><div class="err" role="alert"><?= e($errors['to']) ?></div><?php endif; ?>
        </div>
        <div>
          <label class="lbl" for="date">Travel date</label>
          <input id="date" name="date" class="in" type="date" required
                 min="<?= e(date('Y-m-d')) ?>" max="<?= e(date('Y-m-d', strtotime('+' . MAX_ADVANCE_DAYS . ' days'))) ?>"
                 value="<?= e($submitted && isset($errors['date']) ? ($_GET['date'] ?? '') : $q['date']) ?>">
          <?php if (isset($errors['date'])): ?><div class="err" role="alert"><?= e($errors['date']) ?></div><?php endif; ?>
        </div>
        <div>
          <label class="lbl" for="n">Passengers</label>
          <select id="n" name="n" class="in">
            <?php for ($i = 1; $i <= MAX_PASSENGERS; $i++): ?>
              <option value="<?= $i ?>" <?= $q['n'] === $i ? 'selected' : '' ?>><?= $i ?> passenger<?= $i > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <button class="btn gold" type="submit">Search</button>
      </div>
    </form>
  </section>

  <div class="cols">
    <aside class="side card" aria-label="Filters">
      <h2 class="h2" style="margin:0">Filters</h2>
      <div>
        <span class="lbl" id="cls-lbl">Train class</span>
        <div class="chips" role="radiogroup" aria-labelledby="cls-lbl">
          <label class="chip"><input type="radio" name="class" value="" form="sf" data-autosubmit <?= $q['class'] === '' ? 'checked' : '' ?>><span>All</span></label>
          <?php foreach ($classes as $c): ?>
            <label class="chip"><input type="radio" name="class" value="<?= e($c) ?>" form="sf" data-autosubmit <?= $q['class'] === $c ? 'checked' : '' ?>><span><?= e($c) ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="lbl" for="max_fare">Max fare per passenger: <b>RM <span id="max_fare_out"><?= (int) $maxFare ?></span></b></label>
        <input id="max_fare" name="max_fare" form="sf" type="range" min="10" max="150" step="5" value="<?= (int) $maxFare ?>" style="width:100%;min-height:44px" data-autosubmit>
      </div>
      <div>
        <label class="lbl" for="sort">Sort by</label>
        <select id="sort" name="sort" form="sf" class="in" data-autosubmit>
          <option value="dep" <?= $sort === 'dep' ? 'selected' : '' ?>>Departure time</option>
          <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Price (low to high)</option>
          <option value="dur" <?= $sort === 'dur' ? 'selected' : '' ?>>Duration (shortest)</option>
        </select>
      </div>
      <a class="btn ghost" href="train-search.php?<?= e(http_build_query(array_filter(['from' => $q['from'], 'to' => $q['to'], 'date' => $q['date'], 'n' => $q['n']]))) ?>">Reset filters</a>
    </aside>

    <section class="res" aria-live="polite">
      <?php if ($dbError): ?>
        <div class="alert bad" role="alert">We could not load trains right now. Please try again in a moment.</div>
      <?php elseif (!$submitted): ?>
        <div class="card" style="text-align:center"><div class="b" style="font-size:18px">Where are you going?</div>
          <p class="mut" style="margin:6px 0 0">Choose your stations and travel date, then select Search.</p></div>
      <?php elseif ($errors): ?>
        <div class="alert bad" role="alert">Fix the highlighted fields and search again.</div>
      <?php else: ?>
        <div>
          <div class="b" style="font-size:18px"><?= e($stationName[$q['from']]) ?> to <?= e($stationName[$q['to']]) ?></div>
          <div class="mut sm"><?= e(date('D, j M Y', strtotime($q['date']))) ?> &middot; <?= (int) $q['n'] ?> passenger(s) &middot; <?= count($results) ?> train(s) found</div>
        </div>
        <?php if (!$results): ?>
          <div class="card" style="text-align:center"><div class="b" style="font-size:18px">No trains match your search</div>
            <p class="mut" style="margin:6px 0 0">Try a different class, a higher price limit or another date.</p></div>
        <?php endif; ?>

        <?php foreach ($results as $t): ?>
          <article class="card tr">
            <div style="flex:0 1 150px">
              <div class="b" style="font-size:17px"><?= e($t['no']) ?></div>
              <div class="mut sm"><?= e($t['name']) ?></div>
              <span class="tag" style="margin-top:6px"><?= e(duration_text($t['mins'])) ?></span>
            </div>
            <div class="tm">
              <div><b><?= e($t['dep']) ?></b><div class="mut sm"><?= e($stationName[$q['from']]) ?></div></div>
              <div class="ln" aria-hidden="true"></div>
              <div><b><?= e($t['arr']) ?></b><div class="mut sm"><?= e($stationName[$q['to']]) ?><?= $t['next_day'] ? ' (+1 day)' : '' ?></div></div>
            </div>
            <div class="opts">
              <?php foreach ($t['options'] as $o):
                  $enough = $o['free'] >= $q['n'];
                  if ($o['free'] === 0)        { $tagCls = 'tag bad';  $tagTxt = 'Sold out'; }
                  elseif (!$enough)            { $tagCls = 'tag warn'; $tagTxt = 'Only ' . $o['free'] . ' left'; }
                  elseif ($o['free'] <= 10)    { $tagCls = 'tag warn'; $tagTxt = $o['free'] . ' seats left'; }
                  else                         { $tagCls = 'tag ok';   $tagTxt = $o['free'] . ' seats'; }
                  $link = 'seat-selection.php?' . http_build_query([
                      'schedule_id' => $t['schedule_id'], 'date' => $q['date'], 'from' => $q['from'],
                      'to' => $q['to'], 'n' => $q['n'], 'class' => $o['class'],
                  ]);
              ?>
                <div class="opt">
                  <div><div class="b"><?= e($o['class']) ?> &middot; <?= e(money($o['fare'])) ?> per passenger</div>
                    <span class="<?= $tagCls ?>"><?= e($tagTxt) ?></span></div>
                  <?php if ($enough): ?>
                    <a class="btn gold" href="<?= e($link) ?>" aria-label="Select <?= e($t['no']) ?> <?= e($o['class']) ?>">Select</a>
                  <?php else: ?>
                    <button class="btn" disabled>Not enough seats</button>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </article>
        <?php endforeach; ?>
        <p class="mut sm">Fares shown are estimates. The final fare is calculated at checkout.</p>
      <?php endif; ?>
    </section>
  </div>
<?php require __DIR__ . '/../app/views/partials/m2_footer.php'; ?>
