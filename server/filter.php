<?php
  include_once 'functions.php';
  require '../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  function rate_initialize() {
    $allRates = [0,1,2,3,4,5];
    $_SESSION['rates'] = $allRates;
    return $allRates;
  }

  function rate_set(string $rates) {
    //$rates is a stringified array from tree rate component
    $_SESSION['rates'] = json_decode($rates);
    return $_SESSION['rates'];
  }

  function rate_get() {
    if (!isset($_SESSION['rates'])) {
      rate_initialize();
    }
    return $_SESSION['rates'];
  }

  function weeks_initialize() {
    $_SESSION['weeks'] = [currentWeek(), nextWeek()];
  }

  function weeks_set($weeks = []) {
    if (empty($weeks)) { //reset weeks filter
      weeks_initialize();
    } else { //user selected array of weeks
      $unsanitizedWeeks = json_decode($weeks);
      $weeks = array_map('intval', $unsanitizedWeeks);
      $_SESSION['weeks'] = $weeks;
    }
    return weeks_get();
  }

  function weeks_get() {
    if(!isset($_SESSION['weeks'])) {
      weeks_initialize();
    }
    return array_map('intval', $_SESSION['weeks']);
  }


  switch($_SERVER['REQUEST_METHOD']){
    case 'POST':
      update();
      break;
    case 'GET':
      read();
      break;
    case 'PUT':
      init();
      break;
    case 'DELETE':
      delete();
      break;
  }

  function update() {
    // mode: 1 - FOOD, 2 - DEAD, 3 - OWNERSHIP, 4 - ADOPT
    sec_session_continue(); // Our custom secure way of starting a PHP session.
    $check = admin_check();
    if ($_POST['mode'] == 1) {
      if ($_POST['ids'] != "") {
        $_SESSION['food_ids'] = json_decode($_POST['ids']);
      } else {
        $_SESSION['food_ids'] = null;
      }
    } else if ($_POST['mode'] == 2) {
      if ($_POST['ids'] != "") {
        $_SESSION['dead'] = $_POST['ids'];
      } else {
        $_SESSION['dead'] = null;
      }
    } else if ($_POST['mode'] == 3) {
      if ($_POST['ids'] != "") {
        $_SESSION['public'] = $_POST['ids'];
      } else {
        $_SESSION['public'] = null;
      }
    } else if ($_POST['mode'] == 4) {
      if ($_POST['ids'] != "") {
        $_SESSION['adopt'] = $_POST['ids'];
      } else {
        $_SESSION['adopt'] = null;
      }
    } else if ($_POST['mode'] == 5) {
      rate_set($_POST['ids']);
    } else if ($_POST['mode'] == 6) {
      if ($_POST['ids'] != "") {  //user selected array of weeks
        weeks_set($_POST['ids']);
        $foods = calcSeasonFoods(weeks_get());
        $_SESSION['food_ids'] = $foods;
      } else { //reset weeks filter
        weeks_set();
      }
    }

    if (!isset($_SESSION['public'])) {
      $public = "0,1";
      $_SESSION['public'] = $public;
    }
    if (!isset($_SESSION['dead'])) {
      $dead = "0";
      $_SESSION['dead'] = $dead;
    }
    if (!isset($_SESSION['adopt'])) {
      $adopt = "0";
      $_SESSION['adopt'] = $adopt;
    }
    if (!isset($_SESSION['rates'])) {
      rate_initialize();
    }
    if (!isset($_SESSION['weeks'])) {
      weeks_set();
    }
    $foods = [];
    if ($_ENV['DB_SCHEMA_VERSION'] == 'mysql') {
      //NOTE: this condition is not supported
      if (!isset($_SESSION['legacy_food_ids'])) {
        $foods = calcSeasonFoods();
        $_SESSION['legacy_food_ids'] = $foods;
      }
    } else {
      if (!isset($_SESSION['food_ids'])) {
        $_SESSION['food_ids'] = calcSeasonFoods(weeks_get());
      }
    }

    $params = array(
      "code" => 200,
      "ownerships" => $_SESSION['public'],
      "foods" => $_SESSION['food_ids'],
      "weeks" => weeks_get(),
      "dead" => $_SESSION['dead'],
      "adopt" => $_SESSION['adopt'],
      "rates" => $_SESSION['rates'],
    );
    echo json_encode($params);
  }

  function read() {
    sec_session_continue(); // Our custom secure way of starting a PHP session.
    $check = admin_check();
    $public = null;
    $foods = null;
    $dead = null;
    $adopt = null;
    $rates = null;
    if (isset($_SESSION['public'])) {
      $public = $_SESSION['public'];
    } else {
      $public = "0,1";
      $_SESSION['public'] = $public;
    }
    if (isset($_SESSION['dead'])) {
      $dead = $_SESSION['dead'];
    } else {
      $dead = "0";
      $_SESSION['dead'] = $dead;
    }
    if (isset($_SESSION['adopt'])) {
      $adopt = $_SESSION['adopt'];
    } else {
      $adopt = "0";
      $_SESSION['adopt'] = $adopt;
    }
    $rates = rate_get();
    if (isset($_SESSION['food_ids'])) {
      $foods = $_SESSION['food_ids'];
    } else {
      $foods = calcSeasonFoods(weeks_get());
      $_SESSION['food_ids'] = $foods;
    }
    if (isset($_SESSION['weeks'])) {
      $weeks = weeks_get();
    } else {
      $weeks = weeks_set();
    }
    $params = array(
      "code" => 200,
      "ownerships" => $public,
      "foods" => $foods,
      "weeks" => $weeks,
      "dead" => $dead,
      "adopt" => $adopt,
      "rates" => $rates,
    );
    echo json_encode($params);
  }

  function init() {
    sec_session_continue(); // Our custom secure way of starting a PHP session.
    $check = admin_check();
    $public = null;
    $foods = null;
    $dead = null;
    $adopt = null;
    $rates = rate_initialize();
    $public = "0,1";

    $_SESSION['public'] = $public;
    $dead = "0";
    $_SESSION['dead'] = $dead;
    $weeks = weeks_set();
    $foods = calcSeasonFoods($weeks);
    if ($_ENV['DB_SCHEMA_VERSION'] == 'mysql') {
      //NOTE: this condition is not currently supported
      $_SESSION['legacy_food_ids'] = $foods;
    } else {
      $_SESSION['food_ids'] = $foods;
    }
    $adopt = "0";
    $_SESSION['adopt'] = $adopt;
    $params = array(
      "code" => 200,
      "ownerships" => $public,
      "foods" => $foods,
      "weeks" => $weeks,
      "dead" => $dead,
      "adopt" => $adopt,
      "rates" => $rates,
      "weeks" => $weeks,
    );
    echo json_encode($params);
  }

  function delete() {
    sec_session_continue(); // Our custom secure way of starting a PHP session.
    $check = admin_check();
    $public = null;
    $foods = null;
    $dead = null;
    $adopt = null;
    $rates = null;

    $public = "0,1";
    $_SESSION['public'] = $public;
    $dead = "0";
    $_SESSION['dead'] = $dead;
    // $foods = calcSeasonFoods();
    // $_SESSION['food_ids'] = $foods;
    $adopt = "0";
    $_SESSION['adopt'] = $adopt;
    $rates = rate_initialize();
    $weeks = weeks_get();
    $params = array(
      "code" => 200,
      "ownerships" => $public,
      "foods" => $_SESSION['food_ids'],
      "weeks" => $weeks,
      "dead" => $dead,
      "adopt" => $adopt,
      "rates" => $rates,
    );
    echo json_encode($params);
  }

?>
