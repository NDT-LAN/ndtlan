<?php

use GuzzleHttp\Client;
use LauLamanApps\IzettleApi\GuzzleIzettleClient;
use LauLamanApps\IzettleApi\IzettleClientFactory;

$iZettle = new GuzzleIzettleClient(
  new Client(),
  get_setting('izettle_client_id'),
  get_setting('izettle_client_secret')
);

/** @var LauLamanApps\IzettleApi\Client\AccessToken */
$accessToken = NF::$cache->get('accessToken');

if ($accessToken && $accessToken->isExpired()) {
  $accessToken = null;
}

if (!$accessToken) {
  $accessToken = $iZettle->getAccessTokenFromUserLogin(
    get_setting('izettle_username'),
    get_setting('izettle_password')
  );
}

const BASE_URL = 'https://inventory.izettle.com/organizations/%s/inventory/locations/9c8015e0-fe1f-11e6-9657-f263506c99df';
$inventory = json_decode($iZettle->getJson($iZettle->get(sprintf(BASE_URL, 'self'))))->variants;

$productClient = IzettleClientFactory::getProductClient($iZettle);
$products = $productClient->getProducts();

usort($products, function ($a, $b) {
  return strcmp($a->getName(), $b->getName());
});

$forbiddenProducts = json_decode(get_setting('izettle_excluded_products'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick.css"/>
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick-theme.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/kognise/water.css@latest/dist/dark.min.css">
</head>
<body>
  <style>
    body {
      margin: 0!important;
      padding: 0!important;
      max-width: 100vw!important;
    }

    .Kiosk {
      display: flex;
      flex-direction: row;
      justify-content: center;
      width: 100vw;
      height: 100vh;
    }

    .Kiosk__Page--hidden {
      display: none;
    }

    .Kiosk__Page__Header {
      display: flex;
      flex-direction: column;
    }

    .Kiosk__Page__Header > img {
      width: 16rem;
      height: auto;
    }

    .Kiosk__ProductList {
      list-style-type: none;
    }

    .Kiosk__Product {
      display: flex;
      flex-direction: row;
    }

    .Kiosk__Page {
      text-align: center;
    }

    .Kiosk__Page__Header > h2 {
      text-align: center;
    }

    .Kiosk__Page__Header > img {
      margin: 0 auto;
    }


    .Kiosk__Product > h3 {
      width: 100%;
      text-align: center;
    }

    .center {
      display: flex;
      justify-content: center;
    }

    .available {
      color: greenyellow!important;
    }

    .unavailable {
      color: red!important;
    }
  </style>
  <div class="center">
    <img src="https://d1i137u4q32ft6.cloudfront.net/1568748342/mail-logo.png">
  </div>
  <main class="Kiosk">
    <?php foreach ($products as $i => $product) { ?>
      <?php if (in_array($product->getUuid()->toString(), $forbiddenProducts)) continue; ?>
    <article class="Kiosk__Page">
      <div class="Kiosk__Page__Header">
        <h1><?= $product->getName() ?></h1>
        <?php foreach ($product->getImageLookupKeys()->getAll() as $image) { ?>
          <img src="https://image.izettle.com/product/<?= $image->getFilename() ?>">
        <?php } ?>
      </div>
      <ul class="Kiosk__ProductList">
        <?php $variants = $product->getVariants()->getAll(); ?>
        <?php
          usort($variants, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
          });
        ?>
        <?php foreach ($variants as $variant) { ?>
          <?php if (strpos(strtolower($variant->getName()), 'crew') !== false) continue; ?>
          <?php $stock = array_find($inventory, function ($item) use ($variant) {
            return $item->variantUuid === $variant->getUuid()->toString();
          }); ?>
          <li>
          <?php if ($stock && $stock->balance < 1) { ?>
            <del>
          <?php } ?>
            <div class="Kiosk__Product">
              <h3 class="<?= ($stock && $stock->balance < 1) ? 'unavailable' : 'available' ?>">
                <?= $variant->getName() ?>
                (Kr. <?= number_format($variant->getPrice()->getAmount() / 100, 2, ',', ' ') ?>)
              </h3>
            </div>
            <?php if ($stock && $stock->balance < 1) { ?>
              </del>
            <?php } ?>
          </li>
        <?php } ?>
      </ul>
    </article>
    <?php } ?>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js"></script>
  <script src="//cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.Kiosk').slick({
        autoplay: false,
        autoplaySpeed: 5000
      })
    })
  </script>
</body>
</html>
