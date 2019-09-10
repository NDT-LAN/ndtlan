<? global $navData; ?>
<header>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
    <a
      href="/"
      class="navbar-brand"
      title="<?= get_label('Forsiden', 'nb') ?>"
    >
      <img
        src="<?= get_asset('images/logo.svg') ?>"
        alt="<?= get_label('Forsiden', 'nb') ?>"
      >
    </a>


    <button type="button"
            class="navbar-toggler"
            data-toggle="collapse"
            data-target="#navbar-content"
            aria-controls="navbar-content"
            aria-expanded="false"
            aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="navbar-content" class="collapse navbar-collapse">
      <ul class="navbar-nav mr-auto">
        <?= new Helpers\Nav(10020, 2) ?>
      </ul>
    </div>
  </nav>
</header>
