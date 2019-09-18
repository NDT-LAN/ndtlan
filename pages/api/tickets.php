<?php

$tickets = NF::search()
  ->directory(10001)
  ->where('published', true)
  ->where('productType', 'ticket')
  ->sortBy('price', 'asc')
  ->fields(['id', 'name', 'description', 'price'])
  ->fetch();

$tickets = array_map(function ($ticket) {
  $ticket->id = intval($ticket->id);
  $ticket->price = (int)floatval($ticket->price) * 100;
  return $ticket;
}, $tickets);

header('Content-Type: application/json');
die(json_encode($tickets));
