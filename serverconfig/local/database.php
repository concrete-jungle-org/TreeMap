<?php
  date_default_timezone_set('America/New_York');

	function getConnection() {
		$dbh = new PDO("sqlite:/Users/natobyte/Programming/FoodParent2.0/db/tree_parent.sqlite");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbh;
	}
?>
