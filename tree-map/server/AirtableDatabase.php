<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->required(['AT_PERSONAL_ACCESS_TOKEN', 'AT_BASE_ID'])->notEmpty();
$dotenv->load();
require '../vendor/autoload.php';

class AirtableDatabase {
  
  static function initialize() {
    $key   = $_ENV['AT_PERSONAL_ACCESS_TOKEN'];
    $base  = $_ENV['AT_BASE_ID'];
    return new \Guym4c\Airtable\Airtable($key, $base);
  }
}

?>
