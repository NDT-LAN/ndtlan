<?php
  use Helpers\NDT;
  use Carbon\Carbon;

  if (NDT::currentUser()) {
    header('Location: /profil');
    die();
  }

  $allowedFields = [
    'username',
    'mail',
    'firstname',
    'surname',
    'birthday',
    'adresse',
    'zip',
    'password',
    'password-repeat'
  ];

  $success = false;
  $errors = [];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [];
    foreach ($_POST as $key => $value) {
      if (in_array($key, $allowedFields)) {
        if (!strlen($value)) {
          $errors[$key] = 'Feltet er påkrevd';
        }

        $payload[$key] = $value;
      }
    }

    if ($payload['password'] !== $payload['password-repeat']) {
      $errors['password'] = 'Passordene stemmer ikke overens';
    } else {
      unset($payload['password-repeat']);
    }

    $payload['birthday'] = Carbon::createFromFormat('d.m.Y', $payload['birthday'])
      ->startOfDay()
      ->toDateTimeString();

    if (get_customer_data($payload['username'])) {
      $errors['username'] = 'Dette brukernavnet er allerede registrert';
    }

    if (NF::search()
      ->relation('customer')
      ->where('mail', $payload['mail'])
      ->count()) {
      $errors['mail'] = 'Denne e-post adressen er allerede registrert';
    }

    if (strlen($payload['password']) < 6) {
      $errors['password'] = 'Passordet må være på minst 6 tegn';
    }

    if (!count($errors)) {
      $payload['groups'] = 10000;
      NF::$capi->post('relations/customers/customer', ['json' => $payload]);
      $success = true;

      NF::$capi->post('relations/notifications', ['json' => [
        'body' => [
          'name' => $payload['firstname'],
          'username' => $payload['username']
        ],
        'to' => [['mail' => $payload['mail']]],
        'subject' => 'NDT-LAN - Ny konto',
        'template' => 'user_signup'
      ]]);
    }
  }
?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head', ['headContent' => implode(PHP_EOL, [
  '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">',
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>',
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.no.min.js"></script>'
])]) ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container p-4">
  <? if (!$success) { ?>
    <h1 class="mb-3">Opprett konto</h1>

    <form class="card bg-dark p-3" action="?" method="POST">
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fa fa-user"></i></span>
        </div>
        <input value="<?= $payload['username'] ?? null ?>" type="text" name="username" class="form-control <?= $errors['username'] ? 'is-invalid' : '' ?>" placeholder="Brukernavn" aria-label="Brukernavn" required autofocus autocomplete="username">
      </div>

      <? if ($errors['username']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['username'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text">@</span>
        </div>
        <input value="<?= $payload['mail'] ?? null ?>" type="email" name="mail" class="form-control <?= $errors['mail'] ? 'is-invalid' : '' ?>" placeholder="E-post adresse" aria-label="E-post adresse" required autocomplete="email" autocomplete="username">
      </div>

      <? if ($errors['mail']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['mail'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <div class="input-group mb-3">
        <input value="<?= $payload['firstname'] ?? null ?>" type="text" name="firstname" class="form-control" placeholder="Fornavn" aria-label="Fornavn" autocomplete="given-name">
        <input value="<?= $payload['surname'] ?? null ?>" type="text" name="surname" class="form-control" placeholder="Etternavn" aria-label="Etternavn" autocomplete="family-name">
      </div>

      <? if ($errors['firstname']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['firstname'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <? if ($errors['surname']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['surname'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fa fa-birthday-cake"></i></span>
        </div>
        <input value="<?= $post['birthday'] ?? null ?>" type="text" name="birthday" class="form-control datepicker  <?= $errors['birthday'] ? 'is-invalid' : '' ?>" placeholder="Fødselsdag" aria-label="E-post adresse" required>
      </div>

      <? if ($errors['birthday']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['birthday'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fa fa-envelope"></i></span>
        </div>
        <input value="<?= $payload['adresse'] ?? null ?>" type="text" name="adresse" class="form-control <?= $errors['adresse'] ? 'is-invalid' : '' ?>" placeholder="Adresse" aria-label="Adresse" required autocomplete="street-address">
      </div>

      <? if ($errors['adresse']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['adresse'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <div class="input-group mb-3">
        <input value="<?= $payload['zip'] ?? null ?>" type="text" name="zip" class="form-control <?= $errors['zip'] ? 'is-invalid' : '' ?>" placeholder="Postnummer" aria-label="Postnummer" required autocomplete="postal-code">
      </div>

      <? if ($errors['zip']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['zip'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <br>

      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fa fa-key"></i></span>
        </div>
        <input value="<?= $_POST['password'] ?? null ?>" type="password" name="password" class="form-control <?= $errors['password'] ? 'is-invalid' : '' ?>" placeholder="Passord" aria-label="Passord" required autocomplete="password">
      </div>

      <? if ($errors['password']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['password'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fa fa-repeat"></i></span>
        </div>
        <input value="<?= $_POST['password-repeat'] ?? null ?>" type="password" name="password-repeat" class="form-control <?= $errors['password-repeat'] ? 'is-invalid' : '' ?>" placeholder="Gjenta passord" aria-label="Passord" required autocomplete="password">
      </div>

      <? if ($errors['password-repeat']) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $errors['password-repeat'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <? } ?>

      <button class="btn btn-success btn-block" type="submit">
        <i class="fa fa-check"></i>&nbsp;Registrer deg
      </button>
    </form>
    <? } else { ?>
      <div class="alert alert-success mt-3" role="alert">
        <strong>Konto opprettet</strong>
        <p>Vi har sendt deg en e-post med informasjon om hvordan du logger deg inn</p>
      </div>
    <? } ?>
  </div>
  <? get_block('footer') ?>
</body>
</html>
