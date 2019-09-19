<?php

use Helpers\NDT;

global $edit_tools;
$contact_email = trim(get_static_content('footer_content', 'contact_email', 'text'));
$facebook_url = trim(get_static_content('footer_content', 'facebook_url', 'text'));
?>
<footer class="footer footer-dark bg-dark d-flex justify-content-center mt-auto p-3">
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-2 text-center text-white">
        <a href="https://apility.no" target="_blank">
          <img class="apility-logo" src="<?= get_asset('images/poweredby_apility_white.svg') ?>" alt="Apility">
        </a>
      </div>
    </div>
    <? if ($contact_email) { ?>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-2 text-center text-white">
        <a href="mailto:<?= $contact_email ?>">
          <?= get_label('Kontakt oss', 'nb') ?>
        </a>
      </div>
    </div>
    <? } ?>
    <? if ($facebook_url) { ?>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-2 text-center text-white">
        <a href="<?= $facebook_url ?>" target="_blank">
          <?= get_label('NDT-LAN på Facebook', 'nb') ?>
        </a>
      </div>
    </div>
    <? } ?>
    <br>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-2 text-center text-white">
        <p class="h6">Copyright &copy; <?= date('Y') ?> <a href="/">NDT-LAN</a></p>
      </div>
    </div>
  </div>
</footer>
<?= get_codeinject_bodyclose() ?>
<?= $edit_tools ?? null ?>
<? if (!isset($mode) && getenv('ENV') !== 'dev') { ?>
<script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>window.LogRocket && window.LogRocket.init('3hpt0l/ndt-lan');</script>
<? if ($user = NDT::currentUser()) { ?>
<script>
  LogRocket.identify('<?= $user->id ?>', {
    name: '<?= $user->firstname ?> <?= $user->surname ?>',
    email: '<? $user->mail ?>'
  });
</script>
<? } ?>
<? } ?>
