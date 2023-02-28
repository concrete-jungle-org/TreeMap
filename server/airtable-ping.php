<?php
  require_once('../vendor/autoload.php');
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  include_once 'functions.php';
  include_once 'airtable.php';
  include_once 'airtable-test.php';

  switch($_SERVER['REQUEST_METHOD']){
    case 'POST':
      receivePing();
      break;
    case 'GET':
      testPayload(); //TODO: remove only for testing
      break;
    case 'PUT':
      break;
    case 'DELETE':
      break;
  }

  function receivePing() {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $webhook = $data->webhook->id;
    fetchNotice($webhook);

    // send back an HTTP 200 and an empty response body
    // this is not returned until after the server
    // fetches the payload, updates cursor, queries airtable records, updates sqlite
    http_response_code(200);
  }

  function fetchNotice($webhook_id) {
    $airtable_base = $_ENV['AT_BASE_URL'];
    $base_id = $_ENV['AT_BASE_ID'];
    $auth_token = $_ENV['AT_FOOD_MAP_TOKEN'];
    $authorization = "Authorization: Bearer ".$auth_token;

    //GET THE CURSOR POS
    $cursor; // airtable uses this value to track what notifications the server has seen
    $sql = 'SELECT cursor FROM airtable_cursor ORDER BY ROWID DESC LIMIT 1;';

    try {
      $pdo = getFoodMapConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $cursor = $result['cursor'];
      $pdo = null;
    } catch(PDOException $e) {
      print_error("Unable to get cursor position foodmap database");
      print_error(json_encode($e));
      $cursor = 1;
    }


    $requestUri = sprintf("%sbases/%s/webhooks/%s/payloads?cursor=%d", $airtable_base, $base_id, $webhook_id, $cursor);
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $requestUri);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array($authorization));
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLINFO_HEADER_OUT, True); // enable tracking

    $response = curl_exec($curl);
    $payloads = json_decode($response, true);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      print_error($err);
    }

    $updates = new AirtableUpdate($payloads);
    $new_cursor = $updates->cursor;
    $params = array("cursor" => $new_cursor);
    $sql = "INSERT INTO `airtable_cursor` VALUES ( :cursor )";

    try {
      $pdo = getFoodMapConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $pdo = null;
    } catch(PDOException $e) {
      print_error("ERROR Unable to insert into foodmap database");
      print_error($e->getMessage());
    }

    $new_records = $updates->updateRecords();
    print_error(json_encode($new_records));
  }

?>
