<?php

use Helpers\NDT;
use Carbon\Carbon;

$config = NDT::getSeatMap();
$event = NDT::currentEvent();

$totalSeats = 0;
$bookedSeats = 0;

$signupsResponse = NF::$capi->get('relations/signups/entry/' . $event->id);
$signups = json_decode($signupsResponse->getBody());
$reservationsResponse = NF::$capi->get('relations/signups/entry/' . $event->id . '/status/reservation');
$reservations = json_decode($reservationsResponse->getBody());

$signups = array_merge($signups, $reservations);
$user = NDT::currentUser();
$myreservation = null;

foreach ($config->map as $y => $row) {
  foreach ($row as $x => $seat) {
    if ($seat && $seat->type === 'seat') {
      $totalSeats++;
    }

    if (!$seat) {
      $config->map[$y][$x] = (object)[];

      $seat = $config->map[$y][$x];
    }

    $seat->x = $x;
    $seat->y = $y;

    $signup = array_find($signups, function ($signup) use ($seat) {
      if (isset($signup->data->x) && isset($signup->data->y)) {
        return $signup->data->x == $seat->x && $signup->data->y == $seat->y;
      }
    });

    if ($signup) {
      if ($signup->status === 'default') {
        $bookedSeats++;
      }

      if ($signup->customer_id == $user->id) {

        $seat->type = $signup->status === 'reservation' ? 'myreservation' : 'myseat';
        $seat->label .= PHP_EOL . $user->username;
        if ($signup->status === 'reservation') {
          $myreservation = Carbon::parse($signup->expires_at)
            ->timezone('Europe/Oslo')
            ->toDateTimeString();
        }
      } else {
        $seat->type = 'taken';
        $seat->reserved = $signup->status === 'reservation';
        $seat->label .= PHP_EOL . ($signup->status === 'reservation' ? '(reservert)' : (get_customer($signup->customer_id)['username']));
      }
    }
  }
}

$config->totalSeats = $totalSeats;
$config->bookedSeats = $bookedSeats;
$config->availableSeats = $totalSeats - $bookedSeats;
$config->reservation = $myreservation;

header('Content-Type: application/json');
die(json_encode([
  'width' => $config->width,
  'height' => $config->height,
  'availableSeats' => $config->availableSeats,
  'bookedSeats' => $config->bookedSeats,
  'totalSeats' => $config->totalSeats,
  'map' => $config->map
]));
