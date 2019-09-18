<?php

use SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;

$qrcode = new BaconQrCodeGenerator;
$signup = null;
$order = null;

try {
  $signup = json_decode(
    NF::$capi->get('relations/signups/code/' . $url_asset[1])
      ->getBody()
  );
} catch (Exception $ex) {
  $signup = null;
}

if ($signup) {
  $order = json_decode(
    NF::$capi->get('commerce/orders/' . $signup->order_id)
      ->getBody()
  );
}

$validOrder = $order && $order->id && $order->status === 'c';

$code = $validOrder ? $signup->code : 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

$qrimage = $qrcode
  ->format('png')
  ->color(0, 0, 0)
  ->size(360)
  ->errorCorrection('H')
  ->generate($code);

header('Content-Type: image/png');
die($qrimage);
