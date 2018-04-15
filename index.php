<?php
//GET the data by a bounding box

header('Content-Type: application/json');

require "vendor/autoload.php";

require "config.php";


// connect to database
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE);
if ($mysqli->connect_errno) {
  echo $mysqli->connect_error;
  exit(1);
}

// construct and check bbox
$bbox = str_to_bbox($_GET['bbox']);

if (!is_valid_bbox($bbox)) {
  http_response_code(400);
  echo json_encode(["status" => "The specified bounding box is not valid!"]);
  die();
}

// get ways out of the db which lie in the bbox and construct result
$result = (object) new stdClass();

if (!($stmt = $mysqli->prepare("SELECT wayID, fromNodeId, toNodeId FROM oneway WHERE (ABS(latitude) BETWEEN ABS(?) AND ABS(?)) AND (ABS(longitude) BETWEEN ABS(?) and ABS(?))"))) {
  echo $mysqli->error;
  exit(1);
}

if (!($stmt->bind_param("dddd", $bbox->top, $bbox->bottom, $bbox->left, $bbox->right))) {
  echo $stmt->error;
  exit(1);
}

if (!($stmt->execute())) {
  echo $stmt->error;
  exit(1);
}

$result->segments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!($mysqli->close())) {
  echo $mysqli->error;
  exit(1);
}

// return the result
http_response_code(200);
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


function is_valid_bbox($bbox)
{
  if (isset($bbox) && isset($bbox->left) && isset($bbox->bottom) && isset($bbox->right) && isset($bbox->top) &&
      abs($bbox->left) <= 180 && abs($bbox->bottom) <= 90 && abs($bbox->right) <= 180 && abs($bbox->top) <= 90) {
    return true;
  }
  return false;
}

?>
