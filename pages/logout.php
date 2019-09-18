<?php

use Helpers\NDT;

$reservations = NF::search()
  ->relation('signup')
  ->where('status', 'reservation')
  ->where('customer_id', NDT::currentUser()->id)
  ->fetch();

foreach ($reservations as $reservation) {
  try {
    if ($reservation && isset($reservation->id)) {
      NF::$capi->delete('relations/signups/' . $reservation->id);
    }
  } catch (Exception $ex) {
    /* intentionally left blank */
  }
}

if (isset($_COOKIE['remember'])) {
  unset($_COOKIE['remember']);
  setcookie('remember', '', time() - 3600);
}

session_destroy();

header('Location: /');
die();
