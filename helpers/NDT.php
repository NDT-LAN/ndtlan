<?php

namespace Helpers;

use NF;
use Carbon\Carbon;

class NDT {
  public static function currentEvent () {
    $results = NF::search()
      ->directory(10002)
      ->equals('published', 1)
      ->greaterThanOrEqual('event_end', Carbon::now()->toDateTimeString())
      ->fetch();

    return array_shift($results);
  }

  public static function getEvent ($id) {
    $results = NF::search()
      ->directory(10002)
      ->equals('published', 1)
      ->equals('id', $id)
      ->fetch();

    return array_shift($results);
  }

  public static function getSeatMap () {
    return json_decode(get_setting('event_config'));
  }

  public static function guard ($redirect = '/') {
    if (!static::currentUser()) {
      header('Location: /login?redirect=' . $redirect);
      die();
    }
  }

  public static function createToken ($value) {
    $encrypt_method = 'AES-256-CBC';
    $secret_key = get_setting('netflex_api');
    $secret_iv = time();
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $output = openssl_encrypt($value, $encrypt_method, $key, 0, $iv);

    return base64_encode(json_encode(['value' => $output, 'iv' => $iv, ]));
  }

  public static function parseToken ($token) {
    $encrypt_method = 'AES-256-CBC';
    $secret_key = get_setting('netflex_api');
    $token = json_decode(base64_decode($token));
    $key = hash('sha256', $secret_key);
    $iv = $token->iv;
    $output = openssl_decrypt($token->value, $encrypt_method, $key, 0, $iv);

    return $output;
  }

  public static function currentUser () {
    if (isset($_SESSION['netflex_siteuser'])) {
      $user = json_decode(
        json_encode(
          get_customer(
            get_customer_data($_SESSION['netflex_siteuser'], 'id')
          )
        )
      );

      $needsConsent = true;

      if ($user->birthday) {
        $birthday = Carbon::parse($user->birthday);
        $needsConsent = Carbon::now()->diffInYears($birthday) < 18;
      }

      $user->needs_parental_consent = $needsConsent;

      return $user;
    }
  }
}
