<?php
  require '../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  date_default_timezone_set('America/New_York');

  function getSqliteDSN() {
    $root = $_ENV['PATH_TO_REPO'];
    $rel_path = $_ENV['PATH_TO_DB'];
    $file_name = $_ENV['FILE_NAME'];
    $full_path = "$root$rel_path$file_name";
    return "sqlite:$full_path";
  }

  function getMysqlDSN() {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_NAME'];
    return "mysql:host=$host;port=$port;dbname=$dbname";
  }

	function getConnection() {
    if ($_ENV['DB_TYPE'] == 'sqlite') {
      $dsn = getSqliteDSN();
      $dbh = new PDO($dsn);
  		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  		return $dbh;
    } else {
      $dsn = getMysqlDSN();
      $dbh = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
  		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  		return $dbh;
    }
	}
?>
