<?php

use Helpers\NDT;
use Carbon\Carbon;

function getOrder ($user) {
  if (!isset($_SESSION['order_id'])) {
    $response = NF::$capi->post('commerce/orders', ['json' => [
      'customer_id' => $user->id,
      'ip' => $_SERVER['REMOTE_ADDR'],
      'user_agent' => $_SERVER['HTTP_USER_AGENT'],
      'customer_mail' => $user->mail,
      'customer_phone' => '+' . $user->phone_countrycode . $user->phone,
      'status' => 'a',
      'created' => Carbon::now()->timezone('Europe/Oslo')->toDateTimeString()
    ]]);

    $response = json_decode($response->getBody());
    $_SESSION['order_id'] = $response->order_id;
  }

  $order = json_decode(
    NF::$capi->get('commerce/orders/' . $_SESSION['order_id'])
      ->getBody()
  );

  if (isset($order->status) && $order->status === 'c') {
    $_SESSION['order_id'] = null;
    return getOrder($user);
  }

  return $order;
}

try {
  $order = getOrder(NDT::currentUser());
} catch (Exception $ex) {
  http_response_code(500);
  die();
}

header('Content-Type: application/json');

if (!isset($order->status) || $order->status !== 'c') {
  $order->event = NDT::currentEvent();
  $order->user = NDT::currentUser();
  die(json_encode($order));
}

http_response_code(500);
die(json_encode(['message' => 'Internal error']));
