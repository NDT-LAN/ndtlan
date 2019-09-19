<?php
  global $_mode;

  $pagedata = [
    'url' => $page ? $page['url'] : null
  ];
?>
<head>
  <meta charset="UTF-8">
  <title><?= trim($title ?? get_meta_title(), ' -') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="author" content="<?= get_meta_author() ?>">
  <meta name="keywords" content="<?= get_meta_keywords() ?>">
  <meta name="description" content="<?= get_meta_description() ?>">
  <meta name="keywords" content="<?= get_meta_keywords() ?>">
  <?= $og ?? get_block('opengraph') ?>
  <?= get_codeinject_head() ?>

  <? if(isset($_mode) && $_mode === 'edit') { ?>
    <script
      src="https://code.jquery.com/jquery-2.2.4.min.js"
      integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
      crossorigin="anonymous"
    >
    </script>
  <? } else { ?>
    <script
      src="https://code.jquery.com/jquery-3.4.1.min.js"
      integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
      crossorigin="anonymous"
    >
    </script>
  <? } ?>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

  <script src="<?= get_asset('js/main.js') ?>"></script>
  <link rel="stylesheet" href="<?= get_asset('css/main.css') ?>">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

  <? if (isset($headContent)) { ?>
    <?= $headContent ?>
  <? } ?>

  <script>
    window._page = <?= json_encode($pagedata) ?>

    var $buoop = {
      required: { e:0, f:-3, o:-3, s:-1, c:-3 },
      unsupported: true,
      api: 2018.09
    };

    function $buo_f(){
      var e = document.createElement("script");
      e.src = "//browser-update.org/update.min.js";
      document.body.appendChild(e);
    };

    try {
      document.addEventListener("DOMContentLoaded", $buo_f,false)
    } catch(e) {
      window.attachEvent("onload", $buo_f)
    }
  </script>

  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-113796081-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag(){
      dataLayer.push(arguments);
    }

    gtag('js', new Date());
    gtag('config', 'UA-113796081-1');
  </script>
</head>
