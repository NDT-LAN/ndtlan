<?php

use Helpers\NDT;
use Carbon\Carbon;

function getSeat ($x, $y) {
  $config = NDT::getSeatMap();
  foreach ($config->map as $yy => $row) {
    foreach ($row as $xx => $seat) {
      if ($xx == $x && $yy == $y) {
        return $seat;
      }
    }
  }
}

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

  if (!isset($order->id) || $order->status === 'c') {
    $_SESSION['order_id'] = null;
    return getOrder($user);
  }

  return $order;
}

if (isset($_GET['ticket']) && isset($_GET['x']) && isset($_GET['y'])) {
  $x = $_GET['x'];
  $y = $_GET['y'];

  $tickets = NF::search()
    ->directory(10001)
    ->where('id', $_GET['ticket'])
    ->where('published', true)
    ->where('productType', 'ticket')
    ->fields(['id', 'name', 'price'])
    ->fetch();

  $ticket = array_shift($tickets);

  $user = NDT::currentUser();
  $event = NDT::currentEvent();

  $seat = getSeat($x, $y);

  if ($seat->type !== 'seat') {
    http_response_code(400);
    header('Content-typ: application/json');
    die(json_encode([
      'message' => 'Illegal'
    ]));
  }

  $signupsResponse = NF::$capi->get('relations/signups/entry/' . $event->id);
  $signups = json_decode($signupsResponse->getBody());
  $reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
  $reservations = json_decode($reservationsResponse->getBody());

  $signups = array_merge($signups, $reservations);

  $updateSignup = null;
  $originalSignup = null;

  if (!$user) {
    http_response_code(401);
    header('Content-typ: application/json');
    die(json_encode([
      'message' => 'Not authorized'
    ]));
  }

  foreach ($signups as $signup) {
    if (($signup->data->x ?? -1) == $x && ($signup->data->y ?? -1) == $y) {
      if ($signup->status === 'reservation') {
        if ($signup->customer_id == $user->id) {
          $updateSignup = $signup->id;
          break;
        }
      }

      http_response_code(400);
      header('Content-typ: application/json');
      die(json_encode([
        'message' => 'Reserved'
      ]));
    }
  }

  if (!$updateSignup) {
    foreach ($signups as $signup) {
      if ($signup->status === 'reservation' && $signup->customer_id == $user->id) {
        $updateSignup = $signup->id;
        break;
      }
    }
  }

  $order = getOrder($user);

  $payload = [
    'entry_id' => $ticket->id,
    'entry_name' => $ticket->name,
    'no_of_entries' => 1,
    'variant_cost' => $ticket->price,
    'tax_percent' => 1,
    'properties' => [
      'x' => $x,
      'y' => $y
    ]
  ];

  foreach ($order->cart->items as $item) {
    $entry = get_directory_entry($item->entry_id);
    if ($entry && $entry['productType'] == 'ticket') {
      $payload['no_of_entries'] = 0;
      NF::$capi->put('commerce/orders/' . $order->id . '/cart/' . $item->id, ['json' => $payload]);
    }
  }

  $payload['no_of_entries'] = 1;

  $now = Carbon::now()
    ->timezone('Europe/Oslo')
    ->toDateString();

  $expiry = Carbon::now()
    ->timezone('Europe/Oslo')
    ->add(30, 'minutes')
    ->toDateTimeString();

  NF::$capi->post('commerce/orders/' . $order->id . '/cart', ['json' => $payload]);

  $payload = [
    'firstname' => $user->firstname,
    'surname' => $user->surname,
    'customer_id' => $user->id,
    'data' => [
      'x' => $x,
      'y' => $y,
      'Innsjekket' => 'Nei'
    ],
    'order_id' => $order->id,
    'mail' => $user->mail,
    'entry_id' => $event->id,
    'expires_at' => $expiry,
    'created' => $now,
    'updated' => $now,
    'status' => 'reservation',
  ];

  if (!$updateSignup) {
    NF::$capi->post('relations/signups', ['json' => $payload]);
  } else {
    NF::$capi->put('relations/signups/' . $updateSignup, ['json' => $payload]);
  }

  header('Content-Type: application/json');
  die(json_encode($order));
}
