<?php
  use Helpers\NDT;

  if (NDT::currentUser()) {
    header('Location: /profil');
    die();
  }
?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container p-4">
    <h1>Register</h1>
  </div>
  <? get_block('footer') ?>
</body>
</html>
