<?php

use Helpers\NDT;

if (!isset($_SESSION['netflex_siteuser'])) {
  if (isset($_COOKIE['remember'])) {
    $id = NDT::parseToken($_COOKIE['remember']);
    $user = get_customer($id);

    if ($user) {
      $_SESSION['netflex_siteuser'] = $user['mail'];
      $_SESSION['netflex_siteuser_id'] = $user['id'];
      $_SESSION['netflex_siteuser_ip'] = $_SERVER['REMOTE_ADDR'];
      $_SESSION['netflex_sitename'] = $_SERVER['SERVER_NAME'];

      $token = NDT::createToken(NDT::currentUser()->id);

      setcookie(
        'remember',
        $token,
        time() + (86400 * 30 * 12 * 5),
        '*'
      );
    } else {
      setcookie('remember', '', time() - 3600, '*');
    }
  }
}
