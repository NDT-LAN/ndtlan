<?php

  use Helpers\NDT;

  $error = false;
  $success = false;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($userid) && isset($_POST['password']) && isset($_POST['password-repeat'])) {
      if ($_POST['password'] === $_POST['password-repeat']) {
        if (strlen($_POST['password']) >= 6) {
          try {
            NF::$capi->put('relations/customers/auth/force/' . $userid, ['json' => [
              'password' => $_POST['password']
            ]]);

            $success = true;
            $error = false;
          } catch (Exception $ex) {
            $error = 'Det oppstod en intern feil. Vennligst prøv igjen';
          }
        } else {
          $error = 'Passordet må være minst 6 tegn.';
        }
      } else {
        $error = 'Passordene stemmer ikke overens.';
      }
    } else {
      $error = 'Passordene stemmer ikke overens.';
    }

    if ($error !== false) {
      $success === false;
    }
  }
?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container login-container">
    <form class="form-signin" action="?" method="POST">
      <h1>Endre passord</h1>
      <br>
      <? if ($success) { ?>
        <div class="alert alert-success mt-3" role="alert">
          Ditt passord har blitt endret.<br>
        </div>
        <a class="btn btn-secondary btn-block mt-3" href="/login">Trykk her for å logge inn</a>
      <? } else { ?>
        <label
          for="password"
          class="sr-only"
        >
          Nytt passord
        </label>
        <input
          id="password"
          name="password"
          type="password"
          class="form-control"
          placeholder="Nytt password"
          required
          autofocus
        >
        <label
          for="password-repeat"
          class="sr-only"
        >
          Gjenta passord
        </label>
        <input
          id="password-repeat"
          name="password-repeat"
          type="password"
          class="form-control"
          placeholder="Gjenta passord"
          required
        >

        <? if ($error) { ?>
          <br>
          <div class="alert alert-danger mt-3" role="alert">
            <?= $error ?>
          </div>
        <? } ?>

        <button
          class="btn btn-lg btn-secondary btn-block"
          type="submit"
        >
          Endre passord
        </button>
      <? } ?>
    </form>
  </div>
  <? get_block('footer') ?>
</body>
</html>
