<?php
  date_default_timezone_set('America/New_York');

	function getConnection() {
		$dbh = new PDO("sqlite:/home/public/food-map/db/airtable.sqlite");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbh;
	}
?>
