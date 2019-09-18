<?php

use Helpers\NDT;

$signup = json_decode(NF::$capi->get('relations/signups/order/' . $order->id)->getBody());
$signup = array_shift($signup);
$event = NDT::getEvent($signup->entry_id);
?>
<div class="container p-4">
  <? if (isset($title)) { ?>
    <h1>#<?= $title ?></h1>
  <? } else { ?>
    <h1>Takk for kjøpet</h1>
  <? } ?>
  <? if (isset($qr)) { ?>
    <img src="/api/v1/qrcode/<?= $qr ?>" alt="<?= $qr ?>" class="img-fluid mb-3" width="256" height="256">
  <? } ?>
  <p>Vi har registrert følgende kjøp for arrangementet "<strong><?= $event->name ?></strong>" med ordrenr. #<strong><?= $order->register->receipt_order_id ?></strong>.</p>
  <table class="table table-striped table-dark">
    <thead>
      <tr>
        <th scope="col">Varenr.</th>
        <th scope="col">Vare</th>
        <th scope="col">Pris</th>
      </tr>
    </thead>
    <tbody>
      <? foreach ($order->cart->items as $item) { ?>
        <tr>
          <th scope="row"><?= $item->entry_id ?></th>
          <td><?= $item->entry_name ?></td>
          <td><?= number_format($item->original_entries_total ?? $item->entries_total, 2, ',', ' ') ?></td>
        </tr>
      <? } ?>
      <? foreach ($order->discounts as $discount) { ?>
        <tr>
          <th scope="row"><?= $discount->discount_id ?></th>
          <td><?= $discount->label ?></td>
          <td>-<?= $discount->discount * 100 ?>%</td>
        </tr>
      <? } ?>
      <tr>
        <th scope="row"></th>
        <td class="text-right"><strong>Total</strong></td>
        <td><?= number_format($order->order_total, 2, ',', ' ') ?></td>
      </tr>
    </tbody>
  </table>
  <? if (!isset($footer) || $footer !== false) { ?>
    <p>
      Du vil straks motta en kvittering på e-post <?= $order->customer_mail ?>
    </p>
    <p>
      Du finner også se oversikt over alle dine <a href="/profil/billetter">tidligere ordrer her</a>.
    </p>
  <? } ?>
</div>
