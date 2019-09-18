<?php

namespace Helpers;

use Helpers\Inputs\Text;
use Helpers\Inputs\Number;
use Helpers\Inputs\Select;
use Helpers\Inputs\Checkbox;
use Helpers\Inputs\Password;
use Helpers\Inputs\Renderable;
use Helpers\Inputs\Submit;

class Form {
  private $action;
  public $method;
  public $fields;

  public function __construct($action = '?', $method = 'POST', $fields = [])
  {
    $this->action = $action;
    $this->method = $method;
    $this->fields = [];
  }

  public function add (Renderable $field) {
    $this->fields[] = $field;
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function text ($name, $placeholder = '', $value = '', $label = null) {
    $this->add(new Text($name, $placeholder, $value, $label));
    return $this;
  }

  public function password ($name, $placeholder = '', $value = '', $label = null) {
    $this->add(new Password($name, $placeholder, $value, $label));
    return $this;
  }


  public function number ($name, $placeholder = '', $value = 0, $label = null, $min = null, $max = null) {
    $this->add(new Number($name, $placeholder, $value, $label, $min, $max));
    return $this;
  }

  public function select ($name, $options = [], $label = null) {
    $this->add(new Select($name, $options, $label));
    return $this;
  }

  public function checkbox ($name, $checked = false, $label = null) {
    $this->add(new Checkbox($name, $checked, $label));
    return $this;
  }

  public function submit ($label = 'Submit') {
    $this->add(new Submit($label));
    return $this;
  }

  public function render () {
    $children = implode('', array_map(function ($field) {
      return <<<HTML
      <div class="form-group">
        {$field->render()} <br>
    </div>
HTML;
      }, $this->fields)
    );

    return <<<HTML
      <form action="{$this->action}" method="{$this->method}">
        {$children}
      </form>
HTML;
  }

  public function parse () {
    return FormData::create($this);
  }

  public function __toString()
  {
    return $this->render();
  }
}
