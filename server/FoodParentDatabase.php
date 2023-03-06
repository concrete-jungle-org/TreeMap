<?php

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->required(['PATH_TO_REPO', 'PATH_TO_DB', 'FOOD_PARENT_DB'])->notEmpty();
$dotenv->load();
date_default_timezone_set('America/New_York');

include_once 'functions.php';

class FoodParentDatabase {

  private function getDSN() {
    $root = $_ENV['PATH_TO_REPO'];
    $rel_path = $_ENV['PATH_TO_DB'];
    $file_name = $_ENV['FOOD_PARENT_DB'];
    $full_path = "$root$rel_path$file_name";
    return "sqlite:$full_path";
  }

  private function getConnection() {
    $dsn = $this->getDSN();
    $dbh = new PDO($dsn);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
  }

  public function query($sql, $params = [], $fetchMode = '') {
    $pdo = $this->getConnection();
    $stmt = $pdo->prepare($sql);
    if (!empty($fetchMode)) {
      $stmt->setFetchMode(PDO::FETCH_CLASS, $fetchMode);
    }
    if (empty($params)) {
      $stmt->execute();
    } else {
      $stmt->execute($params);
    }
    $result = $stmt->fetch();
    $pdo = null;
    return $result;
  }

  public function lastInsertRowId($table) {
    $sql = <<<SQL
      SELECT *
      FROM $table
      ORDER BY rowid DESC
      LIMIT 1
    SQL;
    $pdo = $this->getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch()[0];
    $pdo = null;
    return $result;
  }

}
?>
