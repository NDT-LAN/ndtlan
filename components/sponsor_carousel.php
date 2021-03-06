<?php

  $sponsors = NF::search()
    ->directory(10003)
    ->where('published', 1)
    ->fetch();

  shuffle($sponsors);
  $sponsors = array_values(array_filter($sponsors, function ($sponsor) {
    return isset($sponsor->logo) && isset($sponsor->website);
  }));
?>
<? if (count($sponsors)) { ?>
<div id="<?= $blockhash ?>_carousel" class="carousel slide pb-3" data-ride="carousel">
  <div class="carousel-inner">
    <? foreach ($sponsors as $i => $sponsor) { ?>
      <div class="carousel-item <?= !$i ? 'active' : '' ?>">
        <a href="<?= $sponsor->website ?>" target="_blank">
          <img class="d-block w-100" src="<?= get_cdn_media($sponsor->logo->path, '1200x300', 'rc') ?>" alt="<?= $sponsor->name ?>">
        </a>
      </div>
    <? } ?>
  </div>
</div>
<? } ?>
