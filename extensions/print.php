<?php
  use Helpers\NDT;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('extensions/print_template.php');
    die();
  } else {
    $currentEvent = NDT::currentEvent();

    $query = NF::search()
      ->directory(10002);

    if ($currentEvent) {
      $query = $query->notEquals('id', $currentEvent->id);
    }

    $events = $query->sortBy('id', 'desc')
      ->fetch();

    if ($currentEvent) {
      array_unshift($events, $currentEvent);
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Print</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/kognise/water.css@latest/dist/light.min.css">
</head>
<body>
  <style>
    body {
      margin: 0;
      padding: 2rem;
    }
  </style>
  <h1>
    Skriv ut deltagerliste
  </h1>
  <form action="?" method="POST">
    <label for="event">
      Arrangement
    </label>
    <select id="event" name="event">
      <? foreach ($events as $i => $event) { ?>
        <option value="<?= $event->id ?>" <?= !$i ? 'selected' : null ?>>
          <?= $event->name ?>
        </option>
      <? } ?>
    <select>
    <button type="submit">
      Skriv ut
    </button>
  </form>
</body>
</html>
