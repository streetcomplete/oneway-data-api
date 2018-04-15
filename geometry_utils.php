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
			$lat = $pos2->getY() - $ratio * ($pos2->getY() - $pos1->getY());
			$lon = $pos2->getX() - $ratio * ($pos2->getX() - $pos1->getX());
			return new Point($lon, $lat);
    }
  }
	return null;
}


/**
* @return distance between two points in meters
*/
function get_distance($pos1, $pos2, $EARTH_RADIUS)
{
	return $EARTH_RADIUS * distance(
			deg2rad($pos1->getY()),
			deg2rad($pos1->getX()),
			deg2rad($pos2->getY()),
			deg2rad($pos2->getX()
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

?>
