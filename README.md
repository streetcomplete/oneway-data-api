# Oneway Data API

This is an API to get the latest [oneway data](https://missingroads.skobbler.net/dumps/OneWays/) from [improveosm.org](https://improveosm.org)

It consists mainly of two files:

#### `update.php`
- Used to get the latest data from the server (should be run daily)
- Invoke for example via cron as `php -f update.php`

#### `web/index.php`
- Used to get the data for a specified bounding box
- The bounding box must be passed as an URL parameter
- Example: [`/index.php/?bbox=18,-34,19,-33`](https://ent8r.lima-city.de/oneway-data-api/index.php?bbox=18,-34,19,-33) gets all ways in Cape Town

## Configuration & Deployment

Create a `web/config.php` file from the `web/config.sample.php` template and fill it with your MySQL DB settings. Don't forget to create the respective DB and user beforehand.

Everything the web API needs is contained in the `web/` folder. Let your webserver serve this directory as the document root.

## Getting started

* [Website for testing](https://ent8r.lima-city.de/oneway-data-api/)
* [Issue #1022 of StreetComplete](https://github.com/westnordost/StreetComplete/issues/1022) (The reason why this repository was created)
