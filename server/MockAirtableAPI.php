<?php
require '../vendor/autoload.php';
include_once 'functions.php';


class MockAirtableAPI {

  private function getAuthHeader() {
    $authToken = 'DUMMY_TOKEN';
    return "Authorization: Bearer ".$authToken;
  }

  public function fetch($urlSuffix) {
    if (true) { //check url is payloads/
      print_error("::MOCK FETCH::");
      print_error($urlSuffix);
      //mock payloads saved in ../test dir
      $response = file_get_contents("../test/payloads.json");
      return json_decode($response, true);
    }
  }

}
?>

