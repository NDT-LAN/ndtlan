<?php

use Helpers\Nav;
use Helpers\NDT;

global $navData;
global $page;
$homeLabel = get_label('Forsiden', 'nb');
$logo = get_asset('images/logo.svg');
$url = $page['url'];
$url = $url === 'index/' ? '/' : $url;
?>
<header>

  <nav class="navbar navbar-expand-md navbar-dark bg-dark static-top">
    <div class="order-0 pl-3 pr-3">
      <a href="/" class="navbar-brand mx-auto" title="<?= $homeLabel ?>">
        <img src="<?= $logo ?>" alt="<?= $homeLabel ?>">
      </a>
    </div>
    <div class="navbar-collapse collapse w-100 order-1 order-md-0 dual-collapse2">
      <ul class="navbar-nav mr-auto">
        <?= new Nav(10020, 2) ?>
      </ul>
    </div>
    <div class="navbar-collapse collapse w-100 order-3 dual-collapse2">
      <ul class="navbar-nav ml-auto">
        <? if ($user = NDT::currentUser()) { ?>
        <li class="nav-item">
          <div class="dropdown show">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-user"></i>&nbsp; <?= $user->username ?>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
              <a class="dropdown-item" href="/profil">
                <i class="fa fa-gear"></i>&nbsp; <?= get_label('Min profil', 'nb') ?>
              </a>
              <a class="dropdown-item" href="/profil/billetter">
                <i class="fa fa-ticket"></i>&nbsp; <?= get_label('Mine billetter', 'nb') ?>
              </a>
              <a class="dropdown-item" href="/logout">
                <i class="fa fa-sign-out"></i>&nbsp; <?= get_label('Logg ut', 'nb') ?>
              </a>
            </div>
          </div>
        </li>
        <? } else { ?>
          <li class="nav-item">
            <a class="btn btn-secondary" href="/login?redirect=<?= $url ?>">
              <i class="fa fa-lock"></i>&nbsp; <?= get_label('Logg inn', 'nb') ?>
            </a>
          </li>
        <? } ?>
      </ul>
    </div>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".dual-collapse2">
      <span class="navbar-toggler-icon"></span>
    </button>
  </nav>
</header>
