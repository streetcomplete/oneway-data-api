<?php

class LineString
{
  public $components = array();

  public function __construct($components = array()) {
    if (count($components) == 1) {
      throw new Exception("Cannot construct a LineString with a single point");
    }
    if (!is_array($components)) {
      throw new Exception("Points must be passed as an array");
    }
    foreach ($components as $point) {
      if ($point instanceof Point) {
        $this->components[] = $point;
      }
      else {
        throw new Exception("Cannot create a LineString with non-points");
      }
    }
  }

  function getPoints() {
    $array = array();
    foreach ($this->components as $point) {
      $array[] = $point;
    }
    return $array;
  }
}
