<?php

use Helpers\NDT;

$user = NDT::currentUser();
$event = NDT::currentEvent();
$seating = NDT::getSeatMap();
$signups = NF::$capi->get('relations/signups/entry/' . $event->id);
$signups = json_decode($signups->getBody());
$reservations = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
$reservations = json_decode($reservations->getBody());

$editStyle = 'position: initial; margin-right: 0.5rem';

$refreshSettings = [
  'name' => 'Automatisk oppdater',
  'description' => 'Sett interval for oppdatering',
  'icon' => 'fa fa-clock-o',
  'type' => 'integer',
  'alias' => 'refresh_interval',
  'content_field' => 'text',
  'style' => $editStyle
];

$refreshInterval = get_block_content_string($refreshSettings);

foreach ($seating->map as $y => $row) {
  foreach ($row as $x => $seat) {
    if ($seat) {
      $signup = array_find($signups, function ($signup) use ($x, $y) {
        return $signup->status === 'default' && $signup->data->x == $x && $signup->data->y == $y;
      });

      $reservation = array_find($reservations, function ($reservation) use ($x, $y) {
        return $reservation->status === 'reservation' && $reservation->data->x == $x && $reservation->data->y == $y;
      });

      if ($signup) {
        if ($user && $signup->customer_id == $user->id) {
          $seat->type = 'myseat';
          $seat->label .= PHP_EOL . $user->username;
        } else {
          $customer = get_customer($signup->customer_id);
          $seat->type = 'taken';
          if ($customer) {
            $seat->label .= PHP_EOL . $customer['username'];
          }
        }

        continue;
      }

      if ($reservation) {
        if ($user && $reservation->customer_id == $user->id) {
          $seat->type = 'myreservation';
          $seat->label .= PHP_EOL . $user->username;
        } else {
          $seat->type = 'taken';
          $seat->reserved = true;
          $seat->label .= PHP_EOL . '(reservert)';
        }

        continue;
      }
    }
  }
}

?>
<?= set_edit_btn($refreshSettings) ?>
<div class="d-flex flex-column pt-3 bg-dark">
  <? get_block('seating_explanation') ?>
  <div class="container ndt-seating-container p-4">
    <? foreach ($seating->map as $i => $row) { ?>
      <div class="row ndt-seating-row">
      <? foreach ($row as $j => $seat) { ?>
        <? $class = ''; ?>
        <? if ($seat) { ?>
          <? $class = 'ndt-seat--' . $seat->type; ?>
        <? } ?>
        <button
          class="ndt-seat btn <?= $class ?>"
          data-toggle="tooltip"
          title="<?= $seat ? $seat->label : '' ?>"
          aria-label="<?= $seat ? $seat->label : '' ?>"
          style="<?= (isset($seat->reserved) && $seat->reserved) ? 'opacity: 0.5' : '' ?>"
        ></button>
      <? } ?>
      </div>
    <? } ?>
    </div>
  </div>
</div>
<? if ($refreshInterval && $refreshInterval > 0) { ?>
<script>
  setTimeout(function () {
    window.location.reload()
  }, <?= $refreshInterval ?> * 1000)
</script>
<? } ?>
