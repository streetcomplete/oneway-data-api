<?php

require_once "Point.class.php";
require_once "LineString.class.php";

function get_length($line) {
  $points = $line->getPoints();
  $distance = 0;
  for($i = 0; $i+1 < count($points); $i++)
  {
    $pos1 = $points[$i];
    $pos2 = $points[$i+1];
	$distance += get_distance($pos1, $pos2);
  }
  return $distance;
}

function get_center($line) {
  $length = get_length($line);

  $halfDistance = $length / 2;

  $points = $line->getPoints();
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
      $lat = $pos2->y() - $ratio * ($pos2->y() - $pos1->y());
      $lon = $pos2->x() - $ratio * ($pos2->x() - $pos1->x());
      return new Point($lon, $lat);
    }
  }
  return null;
}

function get_data_string($wkt) {
  $first_paren = strpos($wkt, '(');
  if ($first_paren !== FALSE) {
    return substr($wkt, $first_paren);
  } elseif (strstr($wkt,'EMPTY')) {
    return 'EMPTY';
  } else
    return FALSE;
}

function parse_line_string($data_string) {
  $data_string = trim_parens($data_string);
  if ($data_string == 'EMPTY') return new LineString();

  $parts = explode(',', $data_string);
  $points = array();
  foreach ($parts as $part) {
    $points[] = parse_point($part);
  }
  return new LineString($points);
}

function parse_point($data_string) {
  $data_string = trim_parens($data_string);
  if ($data_string == 'EMPTY') return new Point();

  $parts = explode(' ', $data_string);
  return new Point($parts[0], $parts[1]);
}


/**
* @return distance between two points in meters
*/
function get_distance($pos1, $pos2) {
  return 6371000 * distance(
    deg2rad($pos1->y()),
    deg2rad($pos1->x()),
    deg2rad($pos2->y()),
    deg2rad($pos2->x()
    ));
}

// https://en.wikipedia.org/wiki/Great-circle_navigation#cite_note-2
function distance($φ1, $λ1, $φ2, $λ2) {
  $Δλ = $λ2 - $λ1;

  $y = sqrt(pow(cos($φ2)*sin($Δλ), 2) + pow(cos($φ1)*sin($φ2) - sin($φ1)*cos($φ2)*cos($Δλ), 2));
  $x = sin($φ1)*sin($φ2) + cos($φ1)*cos($φ2)*cos($Δλ);
  return atan2($y, $x);
}


/**
 * Trim the parenthesis and spaces
 */
function trim_parens($str) {
  $str = trim($str);

  // We want to only strip off one set of parenthesis
  if (substr($str,0,strlen('(')) == '(') {
    return substr($str,1,-1);
  }
  else return $str;
}

?>
