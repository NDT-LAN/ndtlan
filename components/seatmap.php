<?php

use Helpers\NDT;

$seating = NDT::getSeatMap();
?>
<div class="container ndt-seating-container">
<? for ($y = 0; $y < $seating->height; $y++) { ?>
  <div class="row ndt-seating-row">
  <? for ($x = 0; $x < $seating->width; $x++) { ?>
    <? $seat = $seating->map[$y][$x] ?? null ?>
    <? $class = $seat ? ('ndt-seat--' . $seat->type) : null; ?>
    <button class="ndt-seat btn <?= $class ?>" title="<?= $seat->label ?>">&nbsp;</button>
  <? } ?>
  </div>
<? } ?>
