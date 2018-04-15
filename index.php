<?php

require "vendor/autoload.php";

$app = new Slim\App();

/*$conn = new mysqli("localhost", "api", "12345678");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}*/

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
