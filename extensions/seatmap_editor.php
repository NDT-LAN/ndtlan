<!DOCTYPE html>
<html lang="nb">
<head>
  <meta charset="UTF-8">
  <title><?= trim($title ?? get_meta_title(), ' -') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="<?= get_asset('css/main.css') ?>">
</head>
<body>
<?php
$eventData = json_decode(get_setting('event_config'));
$seatmap = $eventData->seating;

const SEAT_BOOKABLE = 1;
const SEAT_INVISIBLE = 0;
const MAX_WIDTH = 32;
const MAX_HEIGHT = 32;

$x = 0;
$y = 0;

$seatmapData = [];

foreach ($seatmap as $row) {
  $x = 0;
  $y++;
  $seatmapData[$y] = [];
  $seatCount = array_reduce($row, function ($sum, $seat) { return $sum + $seat; }, 0);
  if (!$seatCount) {
    $y++;
  }
  foreach ($row as $seat) {
    $x++;

    if ($seat === SEAT_INVISIBLE) {
      $x++;
    }

    $seatmapData[$y][$x] = $seat;
  }
}

?>
<div class="container ndt-seating-container">
<? for ($y = 1; $y < MAX_HEIGHT; $y++) { ?>
  <div class="row ndt-seating-row">
  <? for ($x = 1; $x < MAX_WIDTH; $x++) { ?>
    <?php
      if (array_key_exists($y, $seatmapData) && array_key_exists($x, $seatmapData[$y])) {
    ?>
      <? if ($seat === SEAT_BOOKABLE) { ?>
        <div data-type="<?= $seat ?>" class="btn btn-success ndt-seat ndt-seat-editor">&nbsp;</div>
      <? } else if ($seat === SEAT_INVISIBLE) { ?>
        <? $x = $x > 0 ? ($x - 1) : $x ?>
        <div data-type="<?= $seat ?>" class="btn btn-invisible ndt-seat ndt-seat-editor">&nbsp;</div>
      <? } else { ?>
        <div data-type="<?= $seat ?>"class="btn btn-danger ndt-seat ndt-seat-editor">&nbsp;</div>
      <? } ?>
    <? } else { ?>
      <div data-type="0" class="btn btn-invisible ndt-seat ndt-seat-editor">&nbsp;</div>
    <? } ?>
  <? } ?>
  </div>
<? } ?>
<script>
  const elements = document.querySelectorAll('.ndt-seat')
  const container = document.querySelector('.ndt-seating-container')
  let mouseDown = false
  container.addEventListener('mousedown', () => mouseDown = true)
  container.addEventListener('mouseup', () => mouseDown = false)

  const switchSeat = seat => {
    let type = seat.dataset.type
    type++
    if (type > 2) {
      type = 0
    }
    seat.dataset.type = type
    seat.classList.remove('btn-success', 'btn-danger', 'btn-invisible')
    if (type === 0) {
      seat.classList.add('btn-invisible')
    } else if (type === 1) {
      seat.classList.add('btn-success')
    } else {
      seat.classList.add('btn-danger')
    }
  }

  elements.forEach(seat => {
    seat.addEventListener('click', () => {
      switchSeat(seat)
    })
    seat.addEventListener('mousemove', () => {
      if (mouseDown) {
        switchSeat(seat)
      }
    })
  })
</script>
</div>
</body>
</html>
