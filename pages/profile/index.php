<?php

use Helpers\NDT;

NDT::guard('/profil');
$user = NDT::currentUser();
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container p-4">
    <h1><?= $user->username ?></h1>
  </div>
  <? get_block('footer') ?>
</body>
</html>
