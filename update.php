<?php

require "vendor/autoload.php";

require "geometry_utils.php";


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

if (!file_exists($file_name)) {
  echo "There was an issue while updating the data!";
}

?>
