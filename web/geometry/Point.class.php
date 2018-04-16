<?php

class Point
{
  public $coords = array(2);

  public function __construct($x = NULL, $y = NULL) {

    $x = (float) str_replace(',', '.', $x);
    $y = (float) str_replace(',', '.', $y);

    if ($x === NULL && $y === NULL) {
      $this->coords = array(NULL, NULL);
      return;
    }

    if (!is_numeric($x) || !is_numeric($y)) {
      throw new Exception("Cannot construct Point. x and y should be numeric");
    }

    $x = floatval($x);
    $y = floatval($y);

    $this->coords = array($x, $y);
  }
  public function x() {
    return $this->coords[0];
  }
  public function y() {
    return $this->coords[1];
  }
}
