<?php
  use Helpers\NDT;

  if (NDT::currentUser()) {
    get_block('password_change', [
      'userid' => NDT::currentUser()->id
    ]);

    die();
  }

  $success = false;

  if (isset($url_asset[1])) {
    get_block('password_change', [
      'userid' => NDT::parseToken($url_asset[1])
    ]);

    die();
  }

  if (isset($_POST['username'])) {
    $success = true;

    $username = $_POST['username'];
    $field = 'username';

    if (strpos($username, '@') !== false) {
      $field = 'mail';
    }

    $response = NF::$capi->get('search?relation=customer&q='.$field.':'.$username)
      ->getBody();

    $users = json_decode($response);

    $user = array_shift($users->data);

    if ($user) {
      NF::$capi->post('relations/notifications', ['json' => [
        'body' => [
          'name' => $user->firstname,
          'token' => NDT::createToken($user->id)
        ],
        'to' => [['mail' => $user->mail]],
        'subject' => 'NDT-LAN - Tilbakestill passord',
        'template' => 'password_reset'
      ]]);
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
      <h1>Tilbakestill passord</h1>
      <br>
      <? if ($success) { ?>
        <div class="alert alert-success mt-3" role="alert">
          Vi har sendt deg en e-post med informasjon om hvordan du tilbakestiller ditt passord
        </div>
      <? } else { ?>
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

        <button
          class="btn btn-lg btn-secondary btn-block"
          type="submit"
        >
          Tilbakestill
        </button>
      <? } ?>
    </form>
  </div>
  <? get_block('footer') ?>
</body>
</html>
