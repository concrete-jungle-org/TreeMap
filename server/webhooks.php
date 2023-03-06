<?php
  require_once('../vendor/autoload.php');
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  include_once 'FoodParentDatabase.php';
  include_once 'AirtableAPI.php';
  include_once 'WebhookService.php';
  /* include_once 'MockAirtableAPI.php'; */

  switch($_SERVER['REQUEST_METHOD']){
    case 'POST':
      airtableHook();
      break;
  }

  function airtableHook() {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    // NOTE: ideally we would return here and then
    // fetch and execute the payload asynchronously
    $persistence = new FoodParentDatabase();
    $api = new AirtableAPI();
    $service = new WebhookService($data, $persistence, $api);
    $service->executePayload();

    // send back an HTTP 200 and an empty response body
    http_response_code(200);
  }
?>


