<?php
  use Apility\OpenGraph\OpenGraph;

  $og = new OpenGraph();
  $og->addProperty('title', get_meta_title());

  $p = 1;

  if (isset($_GET['page'])) {
    $p = intval($_GET['page']);
  }

  if ($p < 1) {
    $p = 1;
  }

  $tag = $_GET['tag'] ?? null;

  $itemsPerPage = get_setting('article_items_per_page') ?? 5;

  $articleCount = NF::search()
    ->directory(10000)
    ->where('published', true)
    ->count();

  $pages = ceil($articleCount / $itemsPerPage);

  if ($p > $pages) {
    $p = $pages;
  }

  $search = NF::search()
    ->directory(10000)
    ->where('published', true)
    ->fields(['name','author', 'banner', 'intro', 'updated', 'url', 'tags'])
    ->sortBy('id','desc');

  if ($tag) {
    $search  = $search->contains('tags', $tag);
  }

  $searchResult = $search->paginate($p - 1, $itemsPerPage)
    ->fetch();

  $articles = array_map(function ($article) {
    return [
      'title' => $article->name,
      'intro' => $article->intro,
      'banner' => $article->banner ? $article->banner->path : null,
      'updated' => $article->updated,
      'slug' => $article->url,
      'author' => $article->author,
      'tags' => array_filter(explode(',', $article->tags))
    ];
  }, $searchResult);
?>
<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head', ['og' => $og->toMetaTags()]) ?>
<body class="d-flex flex-column">
  <? get_block('navbar') ?>
  <main class="container pt-4 p-3 d-flex flex-column flex-grow-1 justify-content-center">
    <?= get_page_blocks('before_articles') ?>

    <div class="pt-3 pb-3">
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

    </div>

    <?= get_page_blocks('after_articles') ?>
  </main>
  <? get_block('footer') ?>
</body>
</html>
