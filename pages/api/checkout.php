<?php

use Helpers\NDT;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session;

try {
$checkoutData = json_decode(file_get_contents('php://input'));

Stripe::setApiKey(NDT::getStripeSK());

$event = NDT::currentEvent();

$reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
$reservations = json_decode($reservationsResponse->getBody());

$signup = array_find($reservations, function ($signup) {
  return $signup->customer_id == NDT::currentUser()->id;
});

if ($signup) {
  $data = $signup->data;
  $data->Innsjekket = 'Nei';

  if (isset($checkoutData->form)) {
    foreach ($checkoutData->form as $field => $value) {
      $data->{$field} = $value;
    }
  }

  NF::$capi->put('relations/signups/' . $signup->id, ['json' => [
    'expires_at' => Carbon::now()
        ->timezone('Europe/Oslo')
        ->add(10, 'minutes')
        ->toDateTimeString(),
    'data' => $data
  ]]);
} else {
  http_response_code(400);
  die(json_encode([
    'message' => 'Reservation expired'
  ]));
}

$user = NDT::currentUser();

$userUpdate = [];
$allowedFields = [
  'phone',
  'parent_name',
  'parent_phone',
  'birthday',
  'adresse',
  'zip'
];

foreach ($checkoutData as $field => $value) {
  if ($field === 'newsletter') {
    $userUpdate['no_newsletter'] = !$value;
    continue;
  }
  if ($field === 'form') {
    continue;
  }
  if (in_array($field, $allowedFields)) {
    $userUpdate[$field] = $value;
  }
}

if (count($userUpdate)) {
  try {
    NF::$capi->put('relations/customers/customer/' . $user->id, ['json' => $userUpdate]);
  } catch (Exception $ex) {
    // wtf
  }
}

$order = json_decode(
  NF::$capi->get('commerce/orders/' . $_SESSION['order_id'])
    ->getBody()
);

if ($order->status !== 'p') {
  NF::$capi->put('commerce/orders/' . $order->id, ['json' => [
    'customer_id' => $user->id,
    'customer_mail' => $user->mail,
    'customer_phone' => $user->phone
  ]]);

  if (!$order->checkout) {
    NF::$capi->put('commerce/orders/' . $order->id . '/checkout', ['json' => [
      'status' => 'p',
      'checkout_start' => Carbon::now()->timezone('Europe/Oslo')->toDateTimeString(),
      'firstname' => $user->firstname,
      'surname' => $user->surname,
    ]]);
  }
}

$callback = trim($checkoutData->callback, '/');

if (getenv('ENV') !== 'dev') {
  $callback = 'https://www.ndt-lan.no/billett';
}

$options = [
  'customer_email' => $order->customer_mail,
  'success_url' => $callback . '/' . $order->secret,
  'cancel_url' => $callback,
  'payment_method_types' => ['card'],
  'line_items' => array_map(function ($item) {
    return [
      'name' => $item->entry_name,
      'amount' => intval($item->variant_cost) * 100,
      'currency' => 'nok',
      'quantity' => intval($item->no_of_entries)
    ];
  }, $order->cart->items)
];

$session = Session::create($options);

NF::$capi->put('commerce/orders/' . $_SESSION['order_id'] . '/data', ['json' => [
  'data_alias' => 'stripe_session_id',
  'type' => 'text',
  'label' => 'Stripe sesjon id',
  'value' => $session->id
]]);

NF::$capi->put('commerce/orders/' . $_SESSION['order_id'] . '/data', ['json' => [
  'data_alias' => 'seat_label',
  'type' => 'text',
  'label' => 'Plass',
  'value' => NDT::getSeatMap()
    ->map[$signup->data->y][$signup->data->x]
    ->label
]]);

$order = json_decode(
  NF::$capi->get('commerce/orders/' . $_SESSION['order_id'])
    ->getBody()
);

$order->data->stripe_session_id = $session->id;
$order->stripe_public_key = NDT::getStripePK();

header('Content-Type: application/json');
die(json_encode($order));
} catch (Exception $ex) {
  dd($ex);
}
