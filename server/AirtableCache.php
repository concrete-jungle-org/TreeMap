<?php

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->required(['PATH_TO_REPO', 'PATH_TO_DB', 'FILE_NAME'])->notEmpty();
$dotenv->load();
require '../vendor/autoload.php';

class AirtableCache {

  private function getDSN() {
    $root = $_ENV['PATH_TO_REPO'];
    $rel_path = $_ENV['PATH_TO_DB'];
    $file_name = $_ENV['FILE_NAME'];
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
      //todo test this branch
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
