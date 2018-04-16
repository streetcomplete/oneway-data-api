<?php

function get_centroid($line)
{
  $EARTH_RADIUS = 6371000; //m

  $length = $line->greatCircleLength($EARTH_RADIUS);

  $halfDistance = $length / 2;

  if($halfDistance == 0) return $line->pointN(1);

  $distance = 0;
	for($i = 1; $i < $line->numPoints(); $i++)
  {
    $pos1 = $line->pointN($i);
		$pos2 = $line->pointN($i+1);
		$segmentDistance = get_distance($pos1, $pos2, $EARTH_RADIUS);
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
function get_distance($pos1, $pos2, $EARTH_RADIUS)
{
	return $EARTH_RADIUS * distance(
			deg2rad($pos1->y()),
			deg2rad($pos1->x()),
			deg2rad($pos2->y()),
			deg2rad($pos2->x()
			));
}


// https://en.wikipedia.org/wiki/Great-circle_navigation#cite_note-2
function distance($φ1, $λ1, $φ2, $λ2)
{
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
