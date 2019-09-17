<?php
  use Apility\OpenGraph\OpenGraph;
  use Helpers\Form;

  $og = new OpenGraph();
  $og->addProperty('title', get_meta_title());

  $p = 1;

  if (isset($_GET['page'])) {
    $p = intval($_GET['page']);
  }

  if ($p < 1) {
    $p = 1;
  }

  $itemsPerPage = 2;

  $articleCount = NF::search()
    ->directory(10000)
    ->where('published', true)
    ->count();

  $pages = ceil($articleCount / $itemsPerPage);

  if ($p > $pages) {
    $p = $pages;
  }

  $articles = array_map(function ($article) {
    return [
      'title' => $article->name,
      'intro' => $article->intro,
      'banner' => $article->banner ? $article->banner->path : null,
      'updated' => $article->updated,
      'slug' => $article->url,
      'author' => $article->author
    ];
  }, NF::search()
    ->directory(10000)
    ->where('published', true)
    ->fields(['name','author', 'banner', 'intro', 'updated', 'url'])
    ->sortBy('id','desc')
    ->paginate($p - 1, $itemsPerPage)
    ->fetch());
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head', ['og' => $og->toMetaTags()]) ?>
<body class="d-flex flex-column">
  <? get_block('navbar') ?>
  <main class="container pt-4 p-3 d-flex flex-column flex-grow-1 justify-content-center">
    <?= $form ?>
    <?= get_page_blocks('before_articles') ?>
    <? foreach ($articles as $article) { ?>
      <?= get_block('article_card', $article) ?>
    <? } ?>

    <nav aria-label="Artikkel paginering">
      <ul class="pagination">
        <? if ($p > 1) { ?>
          <li class="page-item">
            <a class="page-link bg-dark" href="?page=<?= $p - 1 ?>">← Forrige</a>
          </li>
        <? } ?>
        <? if ($p + 1 <= $pages) { ?>
          <li class="page-item">
            <a class="page-link bg-dark" href="?page=<?= $p + 1 ?>">Neste →</a>
          </li>
        <? } ?>
      </ul>
    </nav>

    <?= get_page_blocks('after_articles') ?>
  </main>
  <? get_block('footer') ?>
</body>
</html>
