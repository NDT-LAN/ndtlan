<?php

use Helpers\NDT;

NDT::guard('/me/tickets');
$user = NDT::currentUser();
$signups = json_decode(
  NF::$capi->get('relations/signups/customer/' . $user->id)
    ->getBody()
, true);

$signups = json_decode(json_encode(array_values($signups)));

foreach ($signups as $signup) {
  try {
    $signup->order = json_decode(
      NF::$capi->get('commerce/orders/' . $signup->order_id)
        ->getBody()
    );
  } catch (Exception $ex) {
    $signup->order = null;
  }

  $signup->event = get_directory_entry($signup->entry_id);
}

$signups = array_filter($signups, function ($signup) {
  return $signup->status === 'default'
    && $signup->id
    && $signup->order
    && $signup->event
    && $signup->order->register
    && $signup->data->Plass;
});

usort($signups, function ($a, $b) {
  return strcmp($b->updated, $a->updated);
});

?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container p-4">
    <table class="table table-striped table-dark">
      <thead>
        <tr>
          <th scope="col">Ordre #</th>
          <th scope="col">Arrangement</th>
          <th scope="col">Plass</th>
        </tr>
      </thead>
      <tbody>
        <? foreach ($signups as $signup) { ?>
          <tr>
            <th scope="row"><?= $signup->order->register->receipt_order_id ?></th>
            <td><?= $signup->event['name'] ?></td>
            <td><?= $signup->data->Plass ?? '' ?></td>
          </tr>
        <? } ?>
      </tbody>
    </table>
  </div>
  <? get_block('footer') ?>
</body>
</html>
