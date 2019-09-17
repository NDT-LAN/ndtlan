<?php

namespace Helpers\Inputs;

class Label implements Renderable {
  private $for;
  private $label;

  public function __construct($for, $label)
  {
    $this->for = $for;
    $this->label = $label;
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function render () {
    return <<<HTML
      <label for="{$this->for}">{$this->label}</label>
HTML;
  }

  public function __toString()
  {
    return $this->render();
  }
}
