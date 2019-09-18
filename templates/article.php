<?php

  use Apility\OpenGraph\OpenGraph;

  global $_mode;

  $og = new OpenGraph();

  $articleId = get_entry_id($url_asset[1], 10000);
  $article = get_directory_entry($articleId);

  // Mock content in preview/edit mode
  if ($_mode) {
    $article = [
      'id' => 1,
      'name' => 'Eksempel artikkel',
      'intro' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      'body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Eleifend quam adipiscing vitae proin sagittis. Urna id volutpat lacus laoreet non curabitur gravida. Tincidunt ornare massa eget egestas purus viverra accumsan in. Pharetra convallis posuere morbi leo. Sit amet risus nullam eget felis eget nunc lobortis mattis. Placerat orci nulla pellentesque dignissim enim sit amet venenatis urna. Posuere ac ut consequat semper viverra nam libero justo laoreet. Aliquet eget sit amet tellus cras adipiscing enim eu. Feugiat nibh sed pulvinar proin gravida hendrerit lectus. Convallis a cras semper auctor neque vitae tempus quam pellentesque. Dolor sed viverra ipsum nunc aliquet bibendum enim facilisis. Non pulvinar neque laoreet suspendisse interdum consectetur libero id faucibus. Et ligula ullamcorper malesuada proin libero nunc consequat interdum. Sit amet luctus venenatis lectus magna fringilla urna. Et tortor consequat id porta nibh. Gravida quis blandit turpis cursus in hac. Lacinia at quis risus sed vulputate odio ut enim. Sem fringilla ut morbi tincidunt augue interdum velit euismod.</p>
      <p>Cursus eget nunc scelerisque viverra mauris in aliquam sem fringilla. Nulla pellentesque dignissim enim sit amet. Nulla facilisi cras fermentum odio. Sit amet nisl purus in. Sociis natoque penatibus et magnis. Malesuada fames ac turpis egestas maecenas. Consequat mauris nunc congue nisi vitae suscipit tellus mauris a. Turpis massa tincidunt dui ut ornare lectus sit amet. Morbi tristique senectus et netus et malesuada fames ac. Pharetra convallis posuere morbi leo urna. Diam sollicitudin tempor id eu nisl nunc. Cursus eget nunc scelerisque viverra mauris in. Diam sollicitudin tempor id eu nisl nunc mi ipsum faucibus.</p>'
    ];
  }

  if (!$article) {
    http_response_code(404);
    require('files/404.php');
    die();
  }

  $og->addProperty('title', $article['name']);

  $banner = 'https://placehold.it/800x350';

  if (!$_mode) {
    $banner = get_cdn_media($article['banner'], '800x400', 'l');
  }

  if ($article['banner']) {
    $og->addProperty('image', $article['banner']);
    $og->addProperty('image:width', 1200);
    $og->addProperty('image:height', 1200);
  }
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head', [
  'title' => $article['name'] . ' - NDT-LAN',
  'og' => $og->toMetaTags()
]) ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>

    <div class="container p-4">
      <? if ($banner) { ?>
      <img class="img-responsive" src="<?= $banner ?>" width="100%">
      <? } ?>

      <h1 class="display-4 mt-4 mb-4"><?= $article['name'] ?></h1>
      <p>
        <?= $article['intro'] ?>
      </p>
      <p>
        <?= $article['body'] ?>
      </p>
    </div>

  <? get_block('footer') ?>
</body>
</html>
