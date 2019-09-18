<?php
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

  <link rel="stylesheet" href="<?= get_asset('css/main.css') ?>">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

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
