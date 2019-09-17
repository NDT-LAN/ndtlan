<?php

namespace Helpers\Inputs;

class Select implements Input {
  private $id;
  public $name;
  private $options;
  private $label;

  public function __construct($name, $options = [], $label = null)
  {
    $this->id = $name . '_' . uniqid();
    $this->name = $name;
    $this->label = $label;

    foreach ($options as $label => $value) {
      $this->option($value, $label);
    }
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function option ($value, $label) {
    $this->options[$label] = $value;
    return $this;
  }

  public function render () {
    $options = '';

    foreach ($this->options as $label => $value) {
      if (!is_scalar($value)) {
        $value = htmlspecialchars(json_encode($value));
      }

      $options .= <<<HTML
      <option value="{$value}">{$label}</option>
HTML;
    }

    $innerHTML = <<<HTML
      <select
        id="{$this->id}"
        name="{$this->name}"
        class="form-control"
      >
        {$options}
      </select>
HTML;

    if ($this->label) {
      return new Label($this->id, $this->label) . $innerHTML;
    }

    return $innerHTML;
  }

  public function parse ($value = null) {
    if (!is_null($value)) {
      $option = null;

      foreach ($this->options as &$alternative) {
        if (!is_scalar($alternative)) {
          if (json_encode($alternative) === htmlspecialchars_decode($value)) {
            $option = $alternative;
          }
        }

        if ($alternative == $value) {
          $option = $alternative;
        }
      }

      if (isset($option)) {
        return $option;
      }
    }
  }

  public function __toString()
  {
    return $this->render();
  }
}
