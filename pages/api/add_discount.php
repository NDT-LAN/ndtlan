<?php

use Helpers\NDT;

header('Conent-Type: application/json');

if (isset($_GET['code'])) {
  $event = NDT::currentEvent();

  $discounts = NF::search()
    ->directory(10010)
    ->where('published', true)
    ->where('event', $event->id)
    ->fetch();

  $code = $_GET['code'];

  $discount = array_find($discounts, function ($discount) use ($code) {
    return $discount->discountCode === $code;
  });

  if ($discount) {
    if ($discount->amount > 0) {
      $order = json_decode(
        NF::$capi->get('commerce/orders/' . $_SESSION['order_id'])
          ->getBody()
      );

      if ($order && $order->id) {
        if (count($order->discounts)) {
          $used = array_find($order->discounts, function ($used) use ($discount) {
            return $used->discount_id === $discount->id;
          });

          if ($used) {
            http_response_code(400);
            die(json_encode(['message' => 'Already used']));
          }
        }

        try {
          NF::$capi->post('commerce/orders/' . $order->id . '/discount', ['json' => [
            'scope' => 'cart',
            'discount_id' => $discount->id,
            'label' => $discount->name,
            'discount' => $discount > 0 ? ($discount->value / 100) : 1,
            'type' => 'percent',
          ]]);

          $discount->amount--;
          if ($discount->amount < 0) {
            $discounts->amount = 0;
          }

          NF::$capi->put('builder/structures/entry/' . $discount->id, ['json' => [
            'amount' => $discount->amount,
            'revision_publish' => true
          ]]);

          http_response_code(200);
          die(json_encode(['message' => 'Discount applied']));

        } catch (Exception $ex) {
          http_response_code(500);
          die(json_encode(['message' => 'An error occured']));
        }
      }
    }
  }
}

http_response_code(404);
die(json_encode(['message' => 'Invalid code']));
