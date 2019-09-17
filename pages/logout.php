<?php

session_destroy();

if (isset($_COOKIE['remember'])) {
  unset($_COOKIE['remember']);
  setcookie('remember', '', time() - 3600);
}

header('Location: /');
die();
