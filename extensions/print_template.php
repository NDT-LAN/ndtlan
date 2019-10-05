<?php
  $event = get_directory_entry($_POST['event']);
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

    .event {
      font-size: 4rem;
    }

    .seating {
      font-size: 5rem;
    }

    .user {
      font-size: 2rem;
    }

    .info {
      font-size: 1.75rem;
    }
  </style>
  <? foreach ($signups as $signup) { ?>
    <? $customer = get_customer($signup->customer_id); ?>
    <div class="page">
      <img src="<?= get_cdn_media('1568748342/mail-logo.png', '320x320', 'rc') ?>">
      <h1 class="event"><?= $event['name'] ?></h1>
      <hr>
      <h2 class="seating"><?= $signup->data->Plass ?></h2>
      <h3 class="user"><?= ucfirst($customer['firstname']) ?> "<?= $customer['username'] ?>" <?= ucfirst($customer['surname']) ?></h3>
      <p class="info">
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
