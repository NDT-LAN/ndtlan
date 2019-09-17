<?php

namespace Helpers\Inputs;

interface Element extends Renderable {
  public function render (Element $child = null);
}
