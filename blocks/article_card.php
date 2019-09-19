<?php

use Carbon\Carbon;

$updated = $updated ? Carbon::parse($updated) : Carbon::now();
$updated = $updated->diffForHumans();
$image = get_cdn_media($banner, '750x300', 'rc');
$url = '/artikkel/' . $slug;
$author = $author ?? 'NDT-LAN';

?>
<div class="card bg-dark mb-4">
  <? if ($banner) { ?>
    <a href="<?= $url ?>">
      <img class="card-img-top" src="<?= $image ?>" alt="<?= $title ?>">
    </a>
  <? } ?>
  <div class="card-body">
    <a href="<?= $url ?>">
      <h2 class="card-title"><?= $title ?></h2>
    </a>
    <p class="card-text"><?= $intro ?></p>
  </div>
  <div class="card-footer">
    <? foreach ($tags as $tag) { ?>
      <a href="?tag=<?=$tag ?>" class="badge badge-secondary p-2">
        <?= $tag ?>
      </a>
    <? } ?>
  </div>
  <div class="card-footer">
    Sist oppdatert <?= $updated ?> av <a href="profile/<?= $author ?>"><?= $author ?></a>
  </div>
</div>
