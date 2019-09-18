<?php

namespace Helpers;

class FormData {
  private $form;
  private $input;
  private $data;

  public function __construct (Form $form, $input = null) {
    $this->form = $form;

    if (is_null($input)) {
      if (isset($GLOBALS['_' . strtoupper($form->method)])) {
        $input = $GLOBALS['_' . strtoupper($form->method)];
      }
    }

    $this->input = $input ?? [];
    $this->parse();
  }

  public static function create(...$args) {
    return new static(...$args);
  }

  public function parse ()
  {
    foreach ($this->form->fields as $field) {
      $value = null;

      if (property_exists($field, 'name')) {
        $this->get($field->name);
      }
    }

    return $this->data;
  }

  public function get ($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    };

    $field = null;

    foreach ($this->form->fields as &$formField) {
      if ($formField->name === $name) {
        $field = $formField;
      }
    }

    if ($field) {
      if (array_key_exists($field->name, $this->input)) {
        $value = $this->input[$field->name];
      }

      $value = $field->parse($value);
      $this->data[$name] = $value;

      return $value;
    }
  }

  public function __get ($name) {
    return $this->get($name);
  }
}
