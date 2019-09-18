<?php

namespace Helpers\Inputs;

class Text implements Input {
  private $id;
  public $name;
  private $placeholder;
  private $value;
  private $label;

  public function __construct($name, $placeholder = '', $value = '', $label = null)
  {
    $this->id = $name . '_' . uniqid();
    $this->name = $name;
    $this->$placeholder = $placeholder;
    $this->value = $value;
    $this->label = $label;
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function render () {
    $innerHTML = <<<HTML
      <input
        id="{$this->id}"
        type="text"
        class="form-control"
        name="{$this->name}"
        placeholder="{$this->placeholder}"
        value="{$this->value}"
      >
HTML;

    if ($this->label) {
      return new Label($this->id, $this->label) . $innerHTML;
    }

    return $innerHTML;
  }

  public function parse ($value = null) {
    if (!is_null($value)) {
      return (string) $value;
    }
  }

  public function __toString()
  {
    return $this->render();
  }
}
