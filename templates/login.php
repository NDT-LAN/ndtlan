<?php
use Helpers\NDT;

global $_mode;
$error = null;
$forgottenPassword = false;
$redirect = $_GET['redirect'] ?? null;
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;
$remember = $_POST['remember'] ?? null;

if (NDT::currentUser()) {
  $redirect = $redirect ? $redirect : '/';
  header('Location: ' . $redirect);
  die();
}

setcookie('remember', '', time() - 3600);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $field = 'username';
  if (strpos($username, '@') !== false) {
    $field = 'mail';
  }

  if (!check_login($username, $password, null, $field)) {
    $error = 'Feil brukernavn eller passord';
    $forgottenPassword = true;
  } else {
    if ($remember) {
      $token = NDT::createToken(NDT::currentUser()->id);

      setcookie(
        'remember',
        $token,
        time() + (86400 * 30 * 12 * 5)
      );
    }

    $redirect = $_POST['redirect']
      ? $_POST['redirect']
      : $redirect;

    if (!$redirect) {
      $redirect = '/';
    }

    header('Location: ' . $redirect);
    die();
  }
}
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container login-container">
    <form class="form-signin" action="?" method="POST">
      <h1><?= get_page_content('title', 'text') ?></h1>
      <br>
      <input
        name="redirect"
        type="hidden"
        value="<?= $redirect ?>"
      >
      <label
        for="username"
        class="sr-only"
      >
        Brukernavn / e-post
      </label>
      <input
        id="username"
        name="username"
        type="text"
        value="<?= $username ?>"
        class="form-control"
        placeholder="Brukernavn / e-post"
        required
        <?= !$username ? 'autofocus' : ''?>
      >
      <label for="password" class="sr-only">Passord</label>
      <input
        id="password"
        name="password"
        type="password"
        class="form-control"
        placeholder="Passord"
        required
        <?= $username ? 'autofocus' : '' ?>
      >
      <? if ($error) { ?>
        <div class="alert alert-danger" role="alert">
          <?= $error ?>
        </div>
      <? } ?>
      <div class="checkbox mb-3">
        <label>
          <input
            name="remember"
            type="checkbox"
            value="remember-me"
            <?= $_mode === 'edit' ? 'disabled' : '' ?>
            <?= $remember ? 'checked' : '' ?>
          > <?= $_mode === 'edit'
            ? get_page_content('remember')
            : strip_tags(get_page_content('remember'));
            ?>
        </label>
      </div>

      <? if ($_mode && $_mode === 'edit') { ?>
          <div class="btn btn-lg btn-secondary btn-block" role="button">
            <?= get_page_content('submit') ?>
          </div>
        <? } else { ?>
          <button
            class="btn btn-lg btn-secondary btn-block"
            type="submit"
          >
          <?= strip_tags(get_page_content('submit')) ?>
          </button>
        <? } ?>

      <? if ($_mode || $forgottenPassword) { ?>
        <br>
        <a href="#">
          <?= get_page_content('forgottenPassword', 'text') ?>
        </a>
      <? } ?>
    </form>
  </div>
  <? get_block('footer') ?>
</body>
</html>
