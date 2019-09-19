<?php

$editStyle = 'position: initial; margin-right: 0.5rem';

$carouselSettings = [
  'name' => 'Rediger slides',
  'description' => 'Legg til eller endre slides her',
  'icon' => 'fa fa-image',
  'type' => 'gallery',
  'alias' => 'carousel_images',
  'content_field' => 'image',
  'max-items' => 9999,
  'style' => $editStyle
];

$slides = get_block_content_list($carouselSettings);
?>
<?= set_edit_btn($carouselSettings) ?>
<div id="<?= $blockhash ?>_carousel" class="carousel slide pb-3" data-ride="carousel">
  <ol class="carousel-indicators">
    <? if (isset($_mode) && !count($slides)) { ?>
      <li data-target="#<?= $blockhash ?>_carousel_indicators" data-slide-to="0" class="active"></li>
    <? } else { ?>
      <? foreach ($sides as $i => $slide) { ?>
        <li data-target="#<?= $blockhash ?>_carousel_indicators" data-slide-to="<?= $i ?>" class="<?= !$i ? 'active' : '' ?>"></li>
      <? } ?>
    <? } ?>
  </ol>
  <div class="carousel-inner">
    <? if (isset($_mode) && !count($slides)) { ?>
      <div class="carousel-item active">
        <img class="d-block w-100" src="https://placehold.it/1200x300" alt="Slide 1">
      </div>
    <? } else { ?>
      <? foreach ($slides as $i => $slide) { ?>
        <div class="carousel-item <?= !$i ? 'active' : '' ?>">
          <img class="d-block w-100" src="<?= get_cdn_media($slide, '1200x300', 'rc') ?>" alt="Slide <?= ($i + 1) ?>">
        </div>
      <? } ?>
    <? } ?>
  </div>
  <a class="carousel-control-prev" href="#<?= $blockhash ?>_carousel_indicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Forrige</span>
  </a>
  <a class="carousel-control-next" href="#<?= $blockhash ?>_carousel_indicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Neste</span>
  </a>
</div>
