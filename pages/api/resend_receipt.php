<?php

$order = json_decode(
  NF::$capi->get('commerce/orders/secret/' . $_GET['order'])
    ->getBody()
);

$signups = json_decode(NF::$capi->get('relations/signups/order/' . $order->id)
  ->getBody()
);

$signup = array_shift($signups);

if ($signup && $signup->status === 'default') {
  $event = get_entry($signup->entry_id);
  $customer = get_customer($signup->customer_id);
  $payload = [
    'body' => [
      'name' => $order->checkout->firstname,
      'seat' => $signup->data->Plass,
      'event' => $event['name'],
      'order' => $signup->code
    ],
    'to' => [['mail' => $order->customer_mail]],
    'subject' => 'NDT-LAN - Kvittering #' . $order->register->receipt_order_id,
    'template' => 'ticket'
  ];

  NF::$capi->post('relations/notifications', ['json' => $payload]);
}
