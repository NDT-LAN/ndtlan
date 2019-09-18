<?php

use Helpers\NDT;

NDT::guard('/profil/billetter');
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

  $events = NF::search()
    ->directory(10002)
    ->where('id', $signup->entry_id)
    ->fetch();

  $signup->event = array_shift($events);
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
            <th scope="row">
              <a href="/profil/billetter/kvittering/<?= $signup->order->secret ?>">
                <?= $signup->order->register->receipt_order_id ?>
              </a>
            </th>
            <td>
            <? if (isset($signup->event->page)) { ?>
              <? $page = get_page($signup->event->page) ?>
              <a href="/<?= $page['url'] ?>">
                <?= $signup->event->name ?>
              </a>
            <? } else { ?>
              <td><?= $signup->event->name ?>
            <? } ?>
            </td>
            <td><?= $signup->data->Plass ?? '' ?></td>
          </tr>
        <? } ?>
      </tbody>
    </table>
  </div>
  <? get_block('footer') ?>
</body>
</html>
