<?php
/** Expects: $pageTitle (string), $step (1 = Search, 2 = Seats) */
$pageTitle = $pageTitle ?? 'RailMY';
$step      = $step ?? 0;
$userName  = $_SESSION['user_name'] ?? 'Passenger';
$initials  = strtoupper(mb_substr($userName, 0, 2));
$stepNames = ['Search', 'Seats', 'Passenger details', 'Payment', 'E-ticket'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | RailMY</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap">
<link rel="stylesheet" href="assets/css/m2.css">
</head>
<body>
<header class="top"><div class="wrap nav">
  <a class="brand" href="<?= e(URL_HOME) ?>">RailMY</a>
  <nav class="links" aria-label="Main">
    <a href="<?= e(URL_HOME) ?>">Home</a>
    <a class="on" href="train-search.php">Search Trains</a>
    <a href="<?= e(URL_HISTORY) ?>">My Bookings</a>
    <a href="<?= e(URL_HELP) ?>">Help</a>
  </nav>
  <a class="me" href="<?= e(URL_PROFILE) ?>"><span class="av"><?= e($initials) ?></span><?= e($userName) ?></a>
</div></header>
<main class="wrap page">
<?php if ($step): ?>
  <ol class="steps" aria-label="Booking progress">
  <?php foreach ($stepNames as $i => $label): $n = $i + 1; ?>
    <li class="<?= $n < $step ? 'done' : ($n === $step ? 'on' : '') ?>"<?= $n === $step ? ' aria-current="step"' : '' ?>><?= $n ?> <?= e($label) ?></li>
  <?php endforeach; ?>
  </ol>
<?php endif; ?>
