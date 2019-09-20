<?php

use Helpers\NDT;
use Carbon\Carbon;

$guest = true;
$user = NDT::currentUser();

if (!isset($url_asset[1])) {
  NDT::guard('/profil');
  $guest = false;
} else {
  $users = NF::search()
    ->relation('customer')
    ->where('username', $url_asset[1])
    ->fetch();

  $user = array_shift($users);

  if (!$user) {
    header('Location: /profil');
    die();
  }
}

$groups = json_decode(
  NF::$capi->get('relations/customers/groups')
    ->getBody()
);

usort($groups, function ($a, $b) {
  return $b->id - $a->id;
});

$role = array_shift($groups);
if ($role) {
  $user->role = $role->name;
} else {
  $user->role = 'Gjester';
}

$user->member_for = Carbon::parse($user->created)->longAbsoluteDiffForHumans(Carbon::now());

$previousEvents = json_decode(
  NF::$capi->get('relations/signups/customer/' . $user->id)
    ->getBody()
);
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>

<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container p-4">
    <h1><i class="fa fa-user"></i>&nbsp; <?= $user->username ?></h1>
    <p>Har vært medlem i <?= $user->member_for ?></p>
    <h5><i class="fa fa-star"></i>&nbsp; <?= $user->role ?></h5>

    <div class="card mt-4 bg-dark">
      <div class="card-header">Har deltatt på:</div>
      <div class="card-body">
        <ul>
        <? foreach ($previousEvents as $event) { ?>
          <li><?= get_directory_entry($event->entry_id)['name'] ?></li>
        <? } ?>
        </ul>
      </div>
    </div>
  </div>
  <? get_block('footer') ?>
</body>

</html>
