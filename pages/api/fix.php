<?php

$signups = collect(NF::get('relations/signups'));
$modified = collect();

foreach ($signups as $signup) {
  $data = json_decode(json_encode($signup->data));
  if (!isset($data->Innsjekket) || !is_string($data->Innsjekket) || !in_array($data->Innsjekket, ['Ja', 'Nei'])) {
    $data->Innsjekket = !isset($data->Innsjekket) ? 'Nei' : ($data->Innsjekket ?? null);
    $data->Innsjekket = !is_string($data->Innsjekket) ? ($data->Innsjekket ? 'Ja' : 'Nei') : ($data->Innsjekket ?? null);
    $data->Innsjekket = $data->Innsjekket !== 'Nei' && strlen($data->Innsjekket) ? 'Ja' : 'Nei';
    $newSignup = json_decode(json_encode($signup));
    $newSignup->data = $data;
    $modified->push($newSignup);
  }
}

foreach ($modified as $i => $signup) {
  ob_start();
  $data = $signup->data;
  NF::$capi->put('relations/signups/' . $signup->id, ['json' => [
    'data' => $data
  ]]);
  echo "$i / " . $modified->count() . "<br>\n";
  ob_flush();
}

ob_end_clean();
