# Oneway Data API

This is an API to get the latest [oneway data](https://missingroads.skobbler.net/dumps/OneWays/) from [improveosm.org](https://improveosm.org)

It consists mainly of two files:

#### `update.php`
- Used to get the latest data from the server (should be run daily)
- Invoke for example via cron as `php -f update.php`

#### `index.php`
- Used to get the data for a specified bounding box
- The bounding box must be passed as an URL parameter
- Example: [`/index.php/?bbox=18,-34,19,-33`](https://www.westnordost.de/oneway-data-api/?bbox=18,-34,19,-33) gets all ways in Cape Town

## Configuration & Deployment

1. Copy everything into a web directory of your choice
2. Create a `config.php` file from the `config.sample.php` template and fill it with your MySQL DB settings. Don't forget to create the respective DB and user beforehand.
3. Set up a daily scheduled execution of update.php (at midnight UTC+0 is good)

## Getting started

* [Website for testing](https://www.westnordost.de/oneway-data-api/)
* [Issue #1022 of StreetComplete](https://github.com/westnordost/StreetComplete/issues/1022) (The reason why this repository was created)
