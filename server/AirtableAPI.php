<?php

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->required(['AT_PERSONAL_ACCESS_TOKEN', 'AT_BASE_ID', 'AT_BASE_URL'])->notEmpty();
$dotenv->load();
date_default_timezone_set('America/New_York');

function getAuthHeader() {
  $authToken = $_ENV['AT_PERSONAL_ACCESS_TOKEN'];
  return "Authorization: Bearer ".$authToken;
}

class AirtableAPI {

  public function fetch($urlSuffix = '') {
    $baseUrl = $_ENV['AT_BASE_URL'];
    $baseId = $_ENV['AT_BASE_ID'];
    $urlPrefix = sprintf("%sbases/%s/", $baseUrl, $baseId);
    $url = $urlPrefix.$urlSuffix;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(getAuthHeader()));
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    /* curl_setopt($curl, CURLINFO_HEADER_OUT, True); // for debugging */
    $response = curl_exec($curl);
    $data = json_decode($response, true);
    /* $err = curl_error($curl); */
    curl_close($curl);
    return $data;
  }

}
?>

