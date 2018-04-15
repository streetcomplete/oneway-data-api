# Oneway Data API

This is an API to get the latest [oneway data](https://missingroads.skobbler.net/dumps/OneWays/) from [improveosm.org](https://improveosm.org)

It consists mainly of two files:

#### `update.php`
- Used to get the latest data from the server (should be run daily)

#### `get.php`
- Used to get the data for a specified bounding box
- The bounding box must be passed as an URL parameter
- Example: [`/get.php/?bbox=18,-34,19,-33`](https://ent8r.lima-city.de/oneway-data-api/get.php?bbox=18,-34,19,-33) gets all ways in Cape Town

## Getting started

* [Website for testing](https://ent8r.lima-city.de/oneway-data-api/)
* [Issue #1022 of StreetComplete](https://github.com/westnordost/StreetComplete/issues/1022) (The reason why this repository was created)
