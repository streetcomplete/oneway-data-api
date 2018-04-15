<?php

require "vendor/autoload.php";

$app = new Slim\App();

/*$conn = new mysqli("localhost", "api", "12345678");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}*/

//Update the data
$app->get("/update", function ($request, $response, $args) {

  $file_name = date("Ymd") . ".csv";
  $file = gzfile("https://missingroads.skobbler.net/dumps/OneWays/directionOfFlow_" . date("Ymd") . ".csv.gz");

  //$data = "wayId;fromNodeId;toNodeId;percentage;status;roadType;theGeom;numberOfTrips\r\n";
  $data = "wayId;fromNodeId;toNodeId;latitude;longitude\r\n";
  foreach ($file as $line) {
    $csv = str_getcsv($line, ";");
    if (isset($csv[4]) && $csv[4] == "OPEN") {
      //The percentage is not needed anymore
      unset($csv[3]);
      //The status too
      unset($csv[4]);
      //The road type also
      unset($csv[5]);
      //and the number of trips as well
      unset($csv[7]);

      $centroid = get_centroid(geoPHP::load($csv[6], 'wkt'));
      unset($csv[6]);
      $csv[3] = $centroid->getY();
      $csv[4] = $centroid->getX();

      $data .= implode(";", $csv) . "\r\n";
    }
  }
  file_put_contents($file_name, $data);
  file_put_contents("latest.csv", $data);

  //add_to_database($file_name);

  if (file_exists($file_name)) {
    return $response->withJson(["status" => "Updated data successfully!"]);
  }
  return $response->withJson(["status" => "There was an issue while updating the data!"]);
});

//GET the data by a bounding box
$app->get("/get/", function ($request, $response, $args) {

  $bbox = str_to_bbox($request->getParams()["bbox"]);

  if (!bbox_is_valid($bbox)) {
    return $response->withStatus(400)->withJson(["status" => "The specified bounding box is not valid!"]);
  }

  $result = (object) new stdClass();
  $result->segments = array();

  foreach (file(date("Ymd") . ".csv") as $line) {
    $csv = str_getcsv($line, ";");

    if (is_numeric($csv[3]) && is_numeric($csv[4])) {
      if(abs($bbox->top) <= abs($csv[3]) && abs($csv[3]) <= abs($bbox->bottom) && abs($bbox->left) <= abs($csv[4]) && abs($csv[4]) <= abs($bbox->right)) {
        // Point is in bounding box
        $data = (object) new stdClass();
        $data->wayId = $csv[0];
        $data->fromNodeId = $csv[1];
        $data->toNodeId = $csv[2];

        $result->segments[] = $data;
      }
    }
  }

  return $response->withJson($result);
});

function str_to_bbox($string)
{
  $array = explode(",", $string);
  $bbox = (object) new stdClass();
  $bbox->left = $array[0];
  $bbox->bottom = $array[1];
  $bbox->right = $array[2];
  $bbox->top = $array[3];
  return $bbox;
}

//TODO: Add BBOX validation here
function bbox_is_valid($bbox)
{
  if (isset($bbox)) {
    return true;
  }
  return false;
}

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

function add_to_database($file_name) {
  global $conn;

  $query = <<<eof
  LOAD DATA INFILE '$file_name'
  INTO TABLE data
  FIELDS TERMINATED BY '|' OPTIONALLY ENCLOSED BY '"'
  LINES TERMINATED BY '\n'
  (wayId,fromNodeId,toNodeId,latitude,longitude)
eof;

  $conn->query($query);
}

$app->run();

?>
