<?php
  $event = get_directory_entry($_GET['event']);
  $signups = NF::search()
    ->relation('signup')
    ->where('entry_id', $event['id'])
    ->limit(10000)
    ->where('status', 'default')
    ->fetch();

  usort($signups, function ($a, $b) {
    if (isset($a->data->x) && isset($a->data->y) && isset($b->data->x) && isset($b->data->y)) {
      if ($a->data->x == $b->data->x) {
        return $a->data->y - $b->data->y;
      }

      return $a->data->x - $b->data->x;
    }

    return strcmp($a->data->Plass, $b->data->Plass);
  });
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Deltagerliste</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/kognise/water.css@latest/dist/light.min.css">
</head>
<body>
  <style>
    html, body {
      margin: 0;
      padding: 0;
    }

    .page {
      width: 21cm;
      height: 29.7cm;
      page-break-before: always;

      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
  </style>
  <? foreach ($signups as $signup) { ?>
    <? $customer = get_customer($signup->customer_id); ?>
    <div class="page">
      <img src="<?= get_cdn_media('1568748342/mail-logo.png', '256x256', 'rc') ?>">
      <h2><?= $event['name'] ?></h2>
      <hr>
      <h1><?= $signup->data->Plass ?></h1>
      <h2><?= $customer['username'] ?></h2>
      <p>
        www.ndt-lan.no
      </p>
    </div>
  <? } ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      window.print();
    });
  </script>
</body>
</html>
