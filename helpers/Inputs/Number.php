<?php

namespace Helpers\Inputs;

class Number implements Input {
  private $id;
  public $name;
  private $placeholder;
  private $value;
  private $label;
  private $min;
  private $max;

  public function __construct($name, $placeholder = '', $value = 0, $label = null, $min = null, $max = null)
  {
    $this->id = $name . '_' . uniqid();
    $this->placeholder = $placeholder;
    $this->name = $name;
    $this->value = $value;
    $this->label = $label;
    $this->min = $min;
    $this->max = $max;
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function render () {
    $min = !is_null($this->min) ? ('min="' . $this->min . '"') : '';
    $max = !is_null($this->max) ? ('max="' . $this->max . '"') : '';

    $innerHTML = <<<HTML
      <input
        id="{$this->id}"
        type="number"
        class="form-control"
        name="{$this->name}"
        placeholder="{$this->placeholder}"
        {$min}
        {$max}
      >
HTML;

    if ($this->label) {
      return new Label($this->id, $this->label) . $innerHTML;
    }

    return $innerHTML;
  }

  public function parse ($value = null) {
    if (!is_null($value) && $value !== '') {
      return (string) $value + 0;
    }
  }

  public function __toString()
  {
    return $this->render();
  }
}
