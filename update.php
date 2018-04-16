<?php

require "vendor/autoload.php";

require "config.php";
require "geometry_utils.php";


// connect to database
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE);
if ($mysqli->connect_errno) {
  echo $mysqli->connect_error;
  exit(1);
}

// prepare for updating database
if (!($mysqli->query("DROP TABLE IF EXISTS oneway_new"))) {
  echo $mysqli->error;
  exit(1);
}

if (!($mysqli->query("CREATE TABLE IF NOT EXISTS oneway_new(
                      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                      wayId BIGINT UNSIGNED,
                      fromNodeId BIGINT UNSIGNED,
                      toNodeId BIGINT UNSIGNED,
                      latitude DOUBLE(10,5),
                      longitude DOUBLE(10,5),
                      INDEX pos (latitude, longitude)
                      )")))
{
  echo $mysqli->error;
  exit(1);
}

if (!($mysqli->query("CREATE TABLE IF NOT EXISTS oneway LIKE oneway_new"))) {
  echo $mysqli->error;
  exit(1);
}

// prepare insert statement
if (!($stmt = $mysqli->prepare("INSERT INTO oneway_new(wayId, fromNodeId, toNodeId, latitude, longitude) VALUES (?, ?, ?, ?, ?)")))
{
  echo $mysqli->error;
  exit(1);
}

if (!($stmt->bind_param("iiidd", $wayId, $fromNodeId, $toNodeId, $latitude, $longitude))) {
  echo $stmt->error;
  exit(1);
}

// using transaction and commit is crucial for acceptable performance during db population
if (!($mysqli->query("START TRANSACTION"))) {
  echo $mysqli->error;
  exit(1);
}

// get new data from server
$file = gzfile("https://missingroads.skobbler.net/dumps/OneWays/directionOfFlow_" . date("Ymd") . ".csv.gz");

$headline = str_getcsv($file[0], ";");
$pos_wayId = array_search("wayId", $headline);
$pos_fromNodeId = array_search("fromNodeId", $headline);
$pos_toNodeId = array_search("toNodeId", $headline);
$pos_status = array_search("status", $headline);
$pos_theGeom = array_search("theGeom", $headline);

if ($pos_wayId === FALSE || $pos_fromNodeId === FALSE || $pos_toNodeId === FALSE || $pos_status === FALSE || $pos_theGeom === FALSE) {
  echo "Error: Input data format changed in an unpredictable manner!";
  exit(1);
}

// convert data into desired format and prepare db operations
foreach ($file as $line) {
  $csv = str_getcsv($line, ";");
  if (isset($csv[$pos_status]) && $csv[$pos_status] == "OPEN") {
    $centroid = get_centroid(geoPHP::load($csv[$pos_theGeom], 'wkt'));
    $wayId = $csv[$pos_wayId];
    $fromNodeId = $csv[$pos_fromNodeId];
    $toNodeId = $csv[$pos_toNodeId];
    $latitude = $centroid->getY();
    $longitude = $centroid->getX();
    if (!($stmt->execute())) {
      echo $stmt->error;
      exit(1);
    }
  }
}

// actually do db operations
if (!($stmt->close())) {
  echo $stmt->error;
  exit(1);
}

if (!($mysqli->query("COMMIT"))) {
  echo $mysqli->error;
  exit(1);
}

// make new data available and clean up old data
if (!($mysqli->query("RENAME TABLE oneway TO oneway_old, oneway_new TO oneway;"))) {
  echo $mysqli->error;
  exit(1);
}

if (!($mysqli->query("DROP TABLE IF EXISTS oneway_old"))) {
  echo $mysqli->error;
  exit(1);
}

if (!($mysqli->close())) {
  echo $mysqli->error;
  exit(1);
}

?>
