<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 20/08/13
 * Time: 11:12
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin\Form;

class FileInput extends Field
{
  public function __construct($name, $required)
  {
    $this->name     = $name;
    $this->required = $required;
  }

  /**
   * @return string
   */
  public function toString()
  {
    $str       = '';
    $nameUp    = $this->name;
    $nameUp[0] = strtoupper($nameUp[0]);

    if (count($this->errors) > 0)
      $str .= '<div class="control-group error">';
    else
      $str .= '<div class="control-group">';

    if ($this->label != null)
    {
      $labelUp    = $this->label;
      $labelUp[0] = strtoupper($labelUp[0]);
      $str .= '<label for="' . $this->name . '" class="control-label">' . $labelUp . ' :</label>';
    }

    $str .= '<div class="controls" >';
    $str .= '<input ' . (!is_null($this->class) ? 'class="' . $this->class . '"' : null) . ' type="file"  name="' . $this->name . '" id="'
        . $this->name . '" ' . ($this->disable ? 'disabled' : null) . '/> ';
    $str .= $this->required == self::FIELD_REQUIRED ? self::requiredStarToString() : null;

    if (count($this->errors) > 0)
    {
      $str .= '<span class="help-inline"> ';
      foreach ($this->errors as $e)
        $str .= $e . ', ';
      $str = substr($str, 0, -2);
      $str .= '</span>';
    }

    $str .= '</div>
        </div> ';

    return $str;
  }
}