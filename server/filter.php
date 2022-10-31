<?php
  include_once 'functions.php';
  require '../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

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
      if ($_POST['ids'] != "") {
        $_SESSION['rates'] = $_POST['ids'];
      } else {
        $_SESSION['rates'] = null;
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
      $rates = "-1,0,1,2,3,4,5";
      $_SESSION['rates'] = $rates;
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
        $foods = calcSeasonFoods();
        $_SESSION['food_ids'] = $foods;
      }
    }

    $params = array(
      "code" => 200,
      "ownerships" => $_SESSION['public'],
      "foods" => $_SESSION['food_ids'],
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
    if (isset($_SESSION['rates'])) {
      $rates = $_SESSION['rates'];
    } else {
      $rates = "-1,0,1,2,3,4,5";
      $_SESSION['rates'] = $rates;
    }
    if (isset($_SESSION['food_ids'])) {
      $foods = $_SESSION['food_ids'];
    } else {
      $foods = calcSeasonFoods();
      $_SESSION['food_ids'] = $foods;
    }
    $params = array(
      "code" => 200,
      "ownerships" => $public,
      "foods" => $foods,
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
    $rates = null;
    $public = "0,1";

    $_SESSION['public'] = $public;
    $dead = "0";
    $_SESSION['dead'] = $dead;
    $foods = calcSeasonFoods();
    if ($_ENV['DB_SCHEMA_VERSION'] == 'mysql') {
      //NOTE: this condition is not currently supported
      $_SESSION['legacy_food_ids'] = $foods;
    } else {
      $_SESSION['food_ids'] = $foods;
    }
    $adopt = "0";
    $_SESSION['adopt'] = $adopt;
    $rates = "-1,0,1,2,3,4,5";
    $_SESSION['rates'] = $rates;
    $params = array(
      "code" => 200,
      "ownerships" => $public,
      "foods" => $foods,
      "dead" => $dead,
      "adopt" => $adopt,
      "rates" => $rates,
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
    $rates = "-1,0,1,2,3,4,5";
    $_SESSION['rates'] = $rates;
    $params = array(
      "code" => 200,
      "ownerships" => $public,
      "foods" => $_SESSION['food_ids'],
      "dead" => $dead,
      "adopt" => $adopt,
      "rates" => $rates,
    );
    echo json_encode($params);
  }

?>
