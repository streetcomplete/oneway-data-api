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

  function startPoint() {
    return $this->pointN(1);
  }

  function endPoint() {
    $last_n = $this->numPoints();
    return $this->pointN($last_n);
  }

  function numPoints() {
    return count($this->components);
  }

  function pointN($n) {
    $n = intval($n);
    if (array_key_exists($n-1, $this->components)) {
      return $this->components[$n-1];
    }
    else {
      return NULL;
    }
  }

  function getPoints() {
    $array = array();
    foreach ($this->components as $point) {
      $array[] = $point;
    }
    return $array;
  }

  public function greatCircleLength($radius = 6371000) {
    $length = 0;
    $points = $this->getPoints();
    for($i=0; $i<$this->numPoints()-1; $i++) {
      $point = $points[$i];
      $next_point = $points[$i+1];
      if (!is_object($next_point)) {continue;}
      // Great circle method
      $lat1 = deg2rad($point->y());
      $lat2 = deg2rad($next_point->y());
      $lon1 = deg2rad($point->x());
      $lon2 = deg2rad($next_point->x());
      $dlon = $lon2 - $lon1;
      $length +=
        $radius *
          atan2(
            sqrt(
              pow(cos($lat2) * sin($dlon), 2) +
                pow(cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dlon), 2)
            )
            ,
            sin($lat1) * sin($lat2) +
              cos($lat1) * cos($lat2) * cos($dlon)
          );
    }
    // Returns length in meters.
    return $length;
  }
}
