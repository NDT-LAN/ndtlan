<?php

namespace Helpers\Inputs;

class Checkbox implements Input {
  private $id;
  public $name;
  private $checked;
  private $label;

  public function __construct($name, $checked = false, $label = null)
  {
    $this->id = $name . '_' . uniqid();
    $this->name = $name;
    $this->checked = $checked;
    $this->label = $label ?? $name;
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function render () {
    $checked = $this->checked ? 'checked' : '';
    $innerHTML = <<<HTML
      <input
        id="{$this->id}"
        type="checkbox"
        class="form-control"
        name="{$this->name}"
        {$checked}
      >
HTML;

    return $innerHTML . new Label($this->id, $this->label);
  }

  public function parse ($value = null) {
    return $value === 'on';
  }

  public function __toString()
  {
    return $this->render();
  }
}
