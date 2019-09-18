<?php

namespace Helpers\Inputs;

interface Input extends Renderable {
  public function render ();
  public function parse($value = null);
  public function __toString();
}
