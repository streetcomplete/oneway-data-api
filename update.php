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
                      longitude DOUBLE(10,5)
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

if (!($stmt->bind_param("iiidd", $wayID, $fromNodeId, $toNodeId, $latitude, $longitude))) {
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

// convert data into desired format and prepare db operations
foreach ($file as $line) {
  $csv = str_getcsv($line, ";");
  if (isset($csv[4]) && $csv[4] == "OPEN") {
    $centroid = get_centroid(geoPHP::load($csv[6], 'wkt'));
    $wayID = $csv[0];
    $fromNodeId = $csv[1];
    $toNodeId = $csv[2];
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
if (!($mysqli->query("RENAME TABLE oneway TO oneway_old, oneway_new To oneway;"))) {
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
