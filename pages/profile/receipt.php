<?php

use Helpers\NDT;

if (isset($url_asset[1])) {
  $order = json_decode(
    NF::$capi->get('commerce/orders/secret/' . $url_asset[1])
      ->getBody()
  );

  $signups = NF::search()
    ->relation('signup')
    ->where('order_id', $order->id)
    ->where('customer_id', NDT::currentUser()->id)
    ->fetch();

  $signup = array_shift($signups);

  if (!$signup || !$order) {
    header('Location: /profil');
  }
} else {
  header('Location: /profil');
  die();
}
?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <? get_block('checkout/order_receipt', [
    'order' => $order,
    'title' => $order->register->receipt_order_id,
    'qr' => $signup->code,
    'footer' => false
  ]) ?>
  <? get_block('footer') ?>
</body>
</html>

