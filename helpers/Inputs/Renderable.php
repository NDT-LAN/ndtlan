<?php

namespace Helpers\Inputs;

interface Renderable {
  public function render();
  public function __toString();
}
