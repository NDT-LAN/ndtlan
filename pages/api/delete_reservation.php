<?php

use Helpers\NDT;

$event = NDT::currentEvent();
$user = NDT::currentUser();
$reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
$reservations = json_decode($reservationsResponse->getBody());

foreach ($reservations as $reservation) {
  if ($reservation->customer_id == $user->id && $reservation->status === 'reservation') {
    NF::$capi->delete('relations/signups/' . $reservation->id);
  }
}
