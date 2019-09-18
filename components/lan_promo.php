<?php

global $_mode;
global $page;

$editStyle = 'position: initial; margin-right: 0.5rem';

$linkSettings = [
  'name' => 'Velg lenke',
  'description' => 'Velg side som skal lenkes til',
  'icon' => 'fa fa-link',
  'type' => 'link',
  'alias' => 'linked_page',
  'content_field' => 'text',
  'style' => $editStyle
];

$countdownSettings = [
  'name' => 'Nedtelling',
  'description' => 'Velg en dato for nedtelling',
  'icon' => 'fa fa-calendar',
  'type' => 'datetime',
  'alias' => 'countdown_target',
  'content_field' => 'text',
  'style' => $editStyle
];

$linked_page = get_block_content_string($linkSettings);
$countDownTarget = get_block_content_string($countdownSettings);
$countDownLabel = '';

if ($countDownTarget) {
  date_default_timezone_set('Europe/Oslo');
  $delta = strtotime($countDownTarget) - time();
  $seconds = $delta % 60;
  $minutes = floor(($delta / 60) % 60);
  $hours = floor($delta / (60 * 60) % 24);
  $days = floor($delta / (60 * 60 * 24));

  $seconds = ($seconds < 10 ? '0' : '') . $seconds;
  $minutes = ($minutes < 10 ? '0' : '') . $minutes;
  $hours = ($hours < 10 ? '0' : '') . $hours;

  $countDownLabel = ($days ? ($days . ' dager, ') : '') . $hours . ':' . $minutes . ':' . $seconds;
}

?>
<div class="jumbotron jumbotron-fluid bg-dark text-center">
  <div class="container">
    <? if ($_mode && $_mode === 'edit') { ?>
      <p class="lead">
        <?= set_edit_btn($countdownSettings) ?>
        <?= set_edit_btn($linkSettings) ?>
      </p>
    <? } ?>
    <? if ($linked_page) { ?>
      <a href="<?= $linked_page ?>">
    <? } ?>
    <h1 class="display-4"><?= get_block_content('title') ?></h1>
    <? if ($linked_page) { ?>
      </a>
    <? } ?>
    <hr class="my-4">
    <p class="lead">
      <?= get_block_content('lead') ?>
    </p>
    <? if ($countDownTarget) { ?>
      <h2
        id="<?= $blockhash ?>_timer"
        data-target="<?= $countDownTarget ?>"
        class="display-5"
        style="font-family: monospace">
        <?= $countDownLabel ?>
      </h2>
      <? $page['add_to_bodyclose'] .= '<script>startCountdown("#' . $blockhash . '_timer")</script>' ?>
    <? } ?>
    <p><?= get_block_content('body') ?></p>
    <p class="lead">
      <? if ($linked_page) { ?>
        <? if ($_mode && $_mode === 'edit') { ?>
          <div class="btn btn-dark btn-lg" role="button">
            <?= get_block_content('button_label') ?>
          </div>
        <? } else { ?>
          <a class="btn btn-dark btn-lg" href="<?= $linked_page ?>" role="button">
            <?= strip_tags(get_block_content('button_label'), 'img,b,strong,i,del') ?>
          </a>
        <? } ?>
      <? } ?>
    </p>
  </div>
</div>
