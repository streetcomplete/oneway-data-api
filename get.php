<?php
//GET the data by a bounding box

require "vendor/autoload.php";

$bbox = str_to_bbox($_GET['bbox']);

if (!bbox_is_valid($bbox)) {
  http_response_code(400);
  echo json_encode(["status" => "The specified bounding box is not valid!"]);
  die();
}

$result = (object) new stdClass();
$result->segments = array();

foreach (file("latest.csv") as $line) {
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

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

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

?>
