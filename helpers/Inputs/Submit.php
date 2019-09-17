<?php

namespace Helpers\Inputs;

class Submit implements Input {
  private $label;

  public function __construct($label = 'Submit')
  {
    $this->label = $label;
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function render () {
    return <<<HTML
      <button
        type="submit"
        class="btn btn-primary"
      >
        {$this->label}
      </button>
HTML;
  }

  public function parse ($value = null) {
    return;
  }

  public function __toString()
  {
    return $this->render();
  }
}
