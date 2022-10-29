<?php
  date_default_timezone_set('America/New_York');

	function getConnection() {
		$dbh = new PDO("sqlite:/Users/natobyte/Programming/FoodParent2.0/db/airtable.sqlite");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbh;
	}
?>
