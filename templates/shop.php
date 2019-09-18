<?php

use Helpers\NDT;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Carbon\Carbon;

global $page;

NDT::guard($page['url']);

$saleAvailable = true;

if (!NDT::currentEvent()) {
  $saleAvailable = false;
}

if ($saleAvailable) {
  if (isset($url_asset[1])) {
    $event = NDT::currentEvent();
    $user = NDT::currentUser();
    $reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
    $reservations = json_decode($reservationsResponse->getBody());

    $order = json_decode(
      NF::$capi->get('commerce/orders/secret/' . $url_asset[1])
        ->getBody()
    );

    if ($order && isset($order->data->stripe_session_id) && $order->status !== 'c') {
      $event = NDT::currentEvent();
      Stripe::setApiKey(get_setting('stripe_private_key'));
      $session = Session::retrieve($order->data->stripe_session_id);
      $payment = PaymentIntent::retrieve($session->payment_intent);

      if ($payment && $payment->status === 'succeeded' && count($payment->charges)) {
        $reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
        $reservations = json_decode($reservationsResponse->getBody());

        $signup = array_find($reservations, function ($signup) {
          return $signup->customer_id == NDT::currentUser()->id;
        });

        $seating = NDT::getSeatMap();

        $data = $signup->data;;
        $data->Plass = $seating->map[$signup->data->y][$signup->data->x]->label;

        NF::$capi->post('relations/signups', ['json' => [
          'firstname' => $signup->firstname,
          'surname' => $signup->surname,
          'customer_id' => $signup->customer_id,
          'order_id' => $order->id,
          'mail' => $signup->mail,
          'entry_id' => $signup->entry_id,
          'created' => $signup->created,
          'updated' => Carbon::now()
            ->timezone('Europe/Oslo')
            ->toDateTimeString(),
          'status' => 'default',
          'data' => $data
        ]]);

        NF::$capi->delete('relations/signups/' . $signup->id);

        NF::$capi->post('commerce/orders/' . $order->id . '/payment', ['json' => [
          'status' => 'paid',
          'amount' => $payment->amount_received / 100,
          'payment_method' => 'stripe',
          'transaction_id' => $payment->charges->data[0]->id
        ]]);

        NF::$capi->put('commerce/orders/' . $order->id . '/register');
        NF::$capi->put('commerce/orders/' . $order->id . '/checkout', ['json' => [
          'checkout_end' => Carbon::now()
            ->timezone('Europe/Oslo')
            ->toDateTimeString()
        ]]);

        NF::$capi->put('commerce/orders/' . $order->id, ['json' => [
          'status' => 'c'
        ]]);

        NF::$capi->put('commerce/orders/' . $order->id . '/data', ['json' => [
          'data_alias' => 'event',
          'type' => 'text',
          'label' => 'Arrangement',
          'value' => $signup->event_id
        ]]);
      }

      $order = json_decode(
        NF::$capi->get('commerce/orders/secret/' . $url_asset[1])
          ->getBody()
      );

      NF::$capi->post('relations/notifications', ['json' => [
        'body' => [
          'name' => $order->customer_firstname,
          'seat' => $signup->data->Plass,
          'event' => $event->name,
          'order' => $signup->code
        ],
        'to' => [['mail' => $order->customer_mail]],
        'subject' => 'NDT-LAN - Kvittering #' . $order->register->receipt_order_id,
        'template' => 'ticket'
      ]]);
    }

    if (!isset($order->id) || !isset($order->status) || $order->status !== 'c') {
      header('Location: /' . $page['url']);
      die();
    }

    $order = json_decode(
      NF::$capi->get('commerce/orders/secret/' . $url_asset[1])
        ->getBody()
    );
  }

  $event = NDT::currentEvent();
}
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <? if ($saleAvailable) { ?>
    <? if (!isset($order) || $order->status !== 'c') { ?>
      <div id="shop" class="container p-4 d-none" :class="{ 'd-block': loaded }">
        <ul class="nav nav-pills nav-pills-secondary nav-fill bg-dark">
          <li
            v-for="(step, i) in steps"
            :key="i"
            class="nav-item"
          >
            <a class="nav-link" :class="{ active: i === currentStep }" href="#" @click="switchTab(i)">
              <i :class="'fa fa-' + step.icon"></i>&nbsp;{{ step.title }}
            </a>
          </li>
        </ul>
        <br>
        <button class="btn btn-secondary btn-block" @click="nextStep" :disabled="!formValidated">
          {{ currentStep === 2 ? 'Fullfør' : 'Gå videre' }} <i class="fa fa-long-arrow-right"></i>
        </button>
        <div class="container bg-dark">
          <template v-if="currentStep === 0">
            <? get_block('checkout/ticket_select') ?>
          </template>
          <template v-if="currentStep === 1">
            <? get_block('checkout/seat_select') ?>
          </template>
          <template v-if="currentStep === 2">
            <? get_block('checkout/payment') ?>
          </template>
        </div>
        <button class="btn btn-secondary btn-block" @click="nextStep" :disabled="!formValidated">
          {{ currentStep === 2 ? 'Fullfør' : 'Gå videre' }} <i class="fa fa-long-arrow-right"></i>
        </button>
      </div>
      <?php
        $page['add_to_bodyclose'] .= '<script>initShop("#shop")</script>';
      ?>
      <script src="https://js.stripe.com/v3"></script>
    <? } else { ?>
      <? get_block('checkout/order_receipt', ['order' => $order]) ?>
    <? } ?>
  <? } else { ?>
    <div class="container p-4">
      <div class="jumbotron jumbotron-fluid bg-dark">
        <div class="container">
          <h1 class="display-4">Salget er avsluttet</h1>
          <p class="lead">Det er ingen arrangementer tilgjengelig</p>
        </div>
      </div>
    </div>
  <? } ?>
  <? get_block('footer') ?>
</body>
</html>

