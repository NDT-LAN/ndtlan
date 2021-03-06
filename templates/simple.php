<? get_block('auth') ?>
<!DOCTYPE html>
<html lang="nb">
<? get_block('head') ?>
<body <?= get_body_class() ?>>
  <? get_block('navbar') ?>
  <div class="container p-4">
    <h1 class="display-4 mt-4 mb-4">
      <?= get_page_content('title') ?>
    </h1>
    <p>
      <?= get_page_content('body') ?>
    </p>
  </div>
  <? get_block('footer') ?>
</body>
</html>
