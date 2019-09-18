<?php

namespace Helpers;

class Nav {
  private $parent_id;
  private $levels;

  public function __construct ($parent_id = 0, $levels = 1) {
    $this->parent_id = $parent_id;
    $this->levels = $levels;
  }

  private function buildFolder ($parent_id, $levels) {
    global $navData;
    global $found_url;

    $output = '';

    if (isset($navData['parents'][$parent_id])) {
      $children = $navData['parents'][$parent_id];
      foreach ($children as $child) {
        $item = $navData['items'][$child];
        $output .= '<a class="dropdown-item" href="/' .$item['url'] . '">' . $item['name']. '</a>';
      }
    }

    return $output;
  }

  private function build($parent_id, $levels, $class = 'navbar-nav mr-auto', $type = 'nav', $root_url = null, $liClass = 'nav-item', $aClass = 'nav-link') {
    global $navData;
    global $found_url;

    $output = '<ul class="navbar-nav mr-auto">';

    if (isset($navData['parents'][$parent_id])) {
      $children = $navData['parents'][$parent_id];
      foreach ($children as $child) {
        $item = $navData['items'][$child];
        if ($item['public'] && $item['visible_nav']) {
          $active = $found_url === $item['url'];
          switch ($item['template']) {
            case 'f':
              $output .= '<li class="nav-item dropdown">';
              $output .= '<a class="nav-link dropdown-toggle" id="dropdown_' . $item['id'] . '" href="#' . $item['name'] . '" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
              $output .= $item['name'];
              $output .= '</a>';
              $output .= '<div class="dropdown-menu" aria-labelledby="dropdown_' . $item['id'] . '">';
              $output .= $this->buildFolder($item['id'], 1);
              $output .= '</div>';
              $output .= '</li>';
              break;
            case 'e':
              $output .= '<li class="nav-item">';
              $output .= '<a class="nav-link" target="_blank" href="' . $item['url'] . '">' . $item['name'] . '</a>';
              break;
            case 'i':
              if ($item['id'] === '10034') {
                if (!NDT::currentEvent()) {
                  break;
                }
              }

              $pageItem = get_page($item['url']);
              if ($pageItem) {
                $output .= '<li class="nav-item">';
                $url = $pageItem ? ('/' . $pageItem['url']) : '#';
                $output .= '<a class="nav-link" href="' . $url . '">' . $item['name'] . '</a>';
                $output .= '</li>';
              }
              break;
            default:
              $output .= '<li class="nav-item ' . ($active ? 'active' : '')  . '">';
              $output .= '<a class="nav-link" href="/' . $item['url'] . '">' . $item['name'] . ($active ? ' <span class="sr-only">(current)</span>' : '') . '</a>';
              $output .= '</li>';
          }
        }
      }
    }

    $output .= '</ul>';

    return $output;
  }

  public function __toString () {
    return $this->build($this->parent_id, $this->levels);
  }
}

/* <ul class="navbar-nav mr-auto">
<li class="nav-item active">
  <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
</li>
<li class="nav-item">
  <a class="nav-link" href="#">Link</a>
</li>
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Dropdown
  </a>
  <div class="dropdown-menu" aria-labelledby="navbarDropdown">
    <a class="dropdown-item" href="#">Action</a>
    <a class="dropdown-item" href="#">Another action</a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#">Something else here</a>
  </div>
</li>
<li class="nav-item">
  <a class="nav-link disabled" href="#">Disabled</a>
</li>
</ul> */
