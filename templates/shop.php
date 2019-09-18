<?php

use Helpers\NDT;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Carbon\Carbon;

global $page;

NDT::guard($page['url']);

$event = NDT::currentEvent();
$saleAvailable = $event !== null;

$now = Carbon::now();
$signupStart = Carbon::parse($event->signupStart);
$saleNotStarted = $signupStart->gt($now);
$saleAvailable = $saleAvailable && !$saleNotStarted;

if (getenv('ENV') === 'dev' && $event) {
  $saleAvailable = true;
  $saleNotStarted = false;
}

$countDownLabel = '';

if ($signupStart) {
  $countDownTarget = $signupStart;
  date_default_timezone_set('Europe/Oslo');
  $delta = strtotime($countDownTarget) - time();
  $seconds = $delta % 60;
  $minutes = floor(($delta / 60) % 60);
  $hours = floor($delta / (60 * 60) % 24);
  $days = floor($delta / (60 * 60 * 24));

  $seconds = ($seconds < 10 ? '0' : '') . $seconds;
  $minutes = ($minutes < 10 ? '0' : '') . $minutes;
  $hours = ($hours < 10 ? '0' : '') . $hours;

  $countDownLabel = ($days ? ($days . ' dager, ') : '') . $hours . ':' . $minutes . ':' . $seconds;
}

if ($saleAvailable) {
  if (isset($url_asset[1])) {
    $user = NDT::currentUser();
    $reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
    $reservations = json_decode($reservationsResponse->getBody());

    $order = json_decode(
      NF::$capi->get('commerce/orders/secret/' . $url_asset[1])
        ->getBody()
    );

    if ($order && isset($order->data->stripe_session_id) && $order->status !== 'c') {
      $apiKey = 'stripe_live_private_key';

      if (getenv('ENV') === 'dev') {
        $apiKey = 'stripe_test_private_key';
      }

      Stripe::setApiKey(get_setting($apiKey));
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


    <main class="container pt-4 p-3 d-flex flex-column flex-grow-1 justify-content-center">
      <div class="jumbotron jumbotron-fluid bg-dark text-center">
        <div class="container">
          <? if ($saleNotStarted) { ?>
            <h1 class="display-4"><?= $event->name ?></h1>
            <hr class="my-4">
            <h2>
              Salget starter om
            </h2>
            <h2
              id="sale_start_timer"
              data-target="<?= $event->signupStart ?>"
              class="display-5"
              style="font-family: monospace"
            >
              <?= $countDownLabel ?>
            </h2>
            <? $page['add_to_bodyclose'] .= '<script>startCountdown("#sale_start_timer", function () { window.location.reload(); })</script>' ?>
          <? } else { ?>
            <? if ($event) { ?>
            <h1 class="display-4"><?= $event->name ?></h1>
            <hr class="my-4">
            <h2>
              Salget er avsluttet
            </h2>
            <? } else { ?>
              <h1 class="display-4">Det er ingen tilgjengelige arrangementer.</h1>
            <? } ?>
          <? } ?>
        </div>
      </div>
    </main>
  <? } ?>
  <? get_block('footer') ?>
</body>
</html>

