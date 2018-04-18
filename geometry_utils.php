<?php

function get_length($points) {
  $distance = 0;
  for($i = 0; $i+1 < count($points); $i++)
  {
    $pos1 = $points[$i];
    $pos2 = $points[$i+1];
	$distance += get_distance($pos1, $pos2);
  }
  return $distance;
}

function get_center($points) {
  $length = get_length($points);

  $halfDistance = $length / 2;

  if($halfDistance == 0) return $points[0];

  $distance = 0;
  for($i = 0; $i+1 < count($points); $i++)
  {
    $pos1 = $points[$i];
    $pos2 = $points[$i+1];
    $segmentDistance = get_distance($pos1, $pos2);
    $distance += $segmentDistance;

    if($distance > $halfDistance)
    {
      $ratio = ($distance - $halfDistance) / $segmentDistance;
      $lat = $pos2[1] - $ratio * ($pos2[1] - $pos1[1]);
      $lon = $pos2[0] - $ratio * ($pos2[0] - $pos1[0]);
      return array($lon, $lat);
    }
  }
  return null;
}

/**
* @return distance between two points in meters
*/
function get_distance($pos1, $pos2) {
  return 6371000 * distance(
    deg2rad($pos1[1]), deg2rad($pos1[0]),
    deg2rad($pos2[1]), deg2rad($pos2[0])
  );
}

// https://en.wikipedia.org/wiki/Great-circle_navigation#cite_note-2
function distance($φ1, $λ1, $φ2, $λ2) {
  $Δλ = $λ2 - $λ1;

  $y = sqrt(pow(cos($φ2)*sin($Δλ), 2) + pow(cos($φ1)*sin($φ2) - sin($φ1)*cos($φ2)*cos($Δλ), 2));
  $x = sin($φ1)*sin($φ2) + cos($φ1)*cos($φ2)*cos($Δλ);
  return atan2($y, $x);
}

function parse_lineString_points($str) {

  $str = trim($str);
  if(strpos($str, "LINESTRING") !== 0) return FALSE;
  $str = trim(substr($str, strlen("LINESTRING")));

  if(substr($str, -1) != ')' || substr($str, 0, 1) != '(') return FALSE;
  $str = substr($str, 1, -1);

  $coords = parse_coordinates($str);
  if($coords === FALSE || count($coords) < 2) return FALSE;
  return $coords;
}

function parse_coordinates($str) {
  $coord_strings = explode(',', $str);
  $coords = array();
  foreach ($coord_strings as $coord_string) {
    $coord = parse_coordinate($coord_string);
    if($coord === FALSE) return FALSE;
    $coords[] = $coord;
  }
  return $coords;
}

function parse_coordinate($str) {
  $floats = explode(' ', trim($str));
  if(count($floats) < 2) return FALSE;
  $x = $floats[0];
  $y = $floats[1];
  if (!is_numeric($x) || !is_numeric($y)) return FALSE;
  return array(floatval($x), floatval($y));
}
?>
