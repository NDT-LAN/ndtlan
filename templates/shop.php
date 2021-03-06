<?php

use Helpers\NDT;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Carbon\Carbon;
use Apility\OpenGraph\OpenGraph;

global $page;

$og = new OpenGraph();
$og->addProperty('title', get_meta_title());

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
      Stripe::setApiKey(NDT::getStripeSK());

      $session = Session::retrieve($order->data->stripe_session_id);
      $payment = PaymentIntent::retrieve($session->payment_intent);

      if ($payment && $payment->status === 'succeeded' && count($payment->charges)) {
        NF::$capi->post('commerce/orders/' . $order->id . '/payment', ['json' => [
          'status' => 'paid',
          'amount' => $payment->amount_received / 100,
          'payment_method' => 'stripe',
          'transaction_id' => $payment->charges->data[0]->id
        ]]);

        sleep(1);

        NF::$capi->put('commerce/orders/' . $order->id . '/register');
        NF::$capi->put('commerce/orders/' . $order->id . '/checkout', ['json' => [
          'checkout_end' => Carbon::now()
            ->timezone('Europe/Oslo')
            ->toDateTimeString()
        ]]);

        NF::$capi->put('commerce/orders/' . $order->id, ['json' => [
          'status' => 'c'
        ]]);

        sleep(1);

        $reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
        $reservations = json_decode($reservationsResponse->getBody());

        $signup = array_find($reservations, function ($signup) {
          return $signup->customer_id == NDT::currentUser()->id;
        });

        $seating = NDT::getSeatMap();

        $data = $signup->data;;
        $data->Plass = $seating->map[$signup->data->y][$signup->data->x]->label;

        $signup_id = json_decode(NF::$capi->post('relations/signups', ['json' => [
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
          ]])->getBody()
        )->signup_id;

        sleep(1);

        NF::$capi->delete('relations/signups/' . $signup->id);
        $signup = json_decode(NF::$capi->get('relations/signups/' . $signup_id)->getBody());
      }

      $order = json_decode(
        NF::$capi->get('commerce/orders/secret/' . $url_asset[1])
          ->getBody()
      );

      NF::$capi->post('relations/notifications', ['json' => [
        'body' => [
          'name' => $order->checkout->firstname,
          'seat' => $signup->data->Plass,
          'event' => $event->name,
          'order' => $signup->code
        ],
        'to' => [['mail' => $order->customer_mail]],
        'subject' => 'NDT-LAN - Kvittering #' . $order->register->receipt_order_id,
        'template' => 'ticket'
      ]]);
    }

    $_SESSION['order_id'] = null;

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
<? get_block('head', ['og' => $og->toMetaTags()]) ?>
<body <?= get_body_class() ?>>
  <? if (!isset($_mode) && getenv('ENV') !== 'dev') { ?>
    <script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
    <script>window.LogRocket && window.LogRocket.init('3hpt0l/ndt-lan');</script>
    <? if ($user = NDT::currentUser()) { ?>
      <script>
        LogRocket.identify('<?= $user->id ?>', {
          name: '<?= $user->firstname ?> <?= $user->surname ?>',
          email: '<? $user->mail ?>'
        });
      </script>
    <? } ?>
  <? } ?>
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
        <button class="btn btn-secondary btn-block" @click="nextStep" :disabled="isBusy || !formValidated">
          <template v-if="isBusy">
            <i class="fa fa-spinner fa-spin"></i>
          </template>
          <template v-else>
            {{ currentStep === 2 ? 'Fullfør' : 'Gå videre' }} <i class="fa fa-long-arrow-right"></i>
          </template>
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
        <button class="btn btn-secondary btn-block" @click="nextStep" :disabled="isBusy || !formValidated">
          <template v-if="isBusy">
            <i class="fa fa-spinner fa-spin"></i>
          </template>
          <template v-else>
            {{ currentStep === 2 ? 'Fullfør' : 'Gå videre' }} <i class="fa fa-long-arrow-right"></i>
          </template>
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
  <div class="container p-3">
    <?= get_page_blocks('blocks') ?>
  </div>
  <? get_block('footer') ?>
</body>
</html>

