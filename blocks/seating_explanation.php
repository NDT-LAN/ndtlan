<?php
  use Helpers\NDT;

  $user = NDT::currentUser();
?>
<div class="container bg-dark">
  <ul class="list-inline pt-3 pl-3 pr-3">
    <? if ($user) { ?>
      <li class="list-inline-item">
        <button class="ndt-seat btn ndt-seat--myseat">&nbsp;</button> Min plass
      </li>
      <li class="list-inline-item">
        <button class="ndt-seat btn ndt-seat--myreservation">&nbsp;</button> Min reservasjon
      </li>
    <? } ?>
    <li class="list-inline-item">
      <button class="ndt-seat btn ndt-seat--seat">&nbsp;</button> Ledig
    </li>
    <li class="list-inline-item">
      <button class="ndt-seat btn ndt-seat--taken">&nbsp;</button> Opptatt
    </li>
    <li class="list-inline-item">
      <button class="ndt-seat btn ndt-seat--info">&nbsp;</button> Inngang / Vakt
    </li>
    <li class="list-inline-item">
      <button class="ndt-seat btn ndt-seat--activity">&nbsp;</button> Aktivitet
    </li>
    <li class="list-inline-item">
      <button class="ndt-seat btn ndt-seat--kiosk">&nbsp;</button> Kiosk
    </li>
    <li class="list-inline-item">
      <button class="ndt-seat btn ndt-seat--scene">&nbsp;</button> Scene
    </li>
  </ul>
</div>
