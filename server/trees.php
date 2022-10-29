<?php
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  include_once 'functions.php';

  switch($_SERVER['REQUEST_METHOD']){
    case 'GET':
      read();
      break;
  }

  function read() {
    sec_session_continue(); // Our custom secure way of starting a PHP session.
    $check = admin_check();
    $sql = "SELECT * FROM `tree` WHERE ";

    if (!$check) {
      $public = "1";
      $dead = "0";
    } else {
      if (isset($_SESSION['dead'])) {
        $dead = $_SESSION['dead'];
      } else {
        $dead = "0";
      }
      if (isset($_SESSION['public'])) {
        $public  = $_SESSION['public'];
      } else {
        $public = "0,1";
      }
    }
    $sql .= "`public` IN (".$public.") ";

    $sql .= "AND `dead` IN (".$dead.") ";

    if (isset($_SESSION['legacy_db'])) {
      // Legacy Food basic filtering, list of ints
      if (isset($_SESSION['legacy_food_ids'])) {
        $sql .= "AND `food` IN (".$_SESSION['legacy_food_ids'].") ";
      } else {
        $foods = calcSeasonFoods();
        $sql .= "AND `food` IN (" . $foods . ") ";
      }
    } else {
      // Airtable Food basic filtering, list of strings
      $foodList = [];
      if (isset($_SESSION['food_ids'])) {
        $foodList = $_SESSION['food_ids'];
      } else {
        $foodList = calcSeasonFoods();
      }

      //In the db Tree.food values look like this: '["recOMoRrvE7GlOJ6M"]'
      //Wrap the food.id to create a query like: `food` IN ('["recOMoRrvE7GlOJ6M"]')
      $wrappedFoods = '';
      foreach ($foodList as $foodId) {
        $wrap = '["'.$foodId.'"]';
        if ($wrappedFoods == '') { // first food only
          $wrappedFoods .= $wrap;
        } else { //subsequent foods
          $wrappedFoods .= "','".$wrap;
        }
      }
      $sql .= "AND `food` IN ('".$wrappedFoods."') ";
    }

    if (isset($_SESSION['adopt'])) {
      if (isset($_SESSION['user_id'])) {
        $userId = intval($_SESSION['user_id']);
        if (intval($_SESSION['adopt']) == 1) {
          $sql .= "AND ( ";
          for ($i = 1; $i <= 10; $i++) {
            if ($i == 1) {
              $sql .= "SUBSTRING_INDEX(`parent`, ',', " . $i . ") = " . $userId . " ";
            } else {
              $sql .= "OR SUBSTRING_INDEX(SUBSTRING_INDEX(`parent`, ',', " . $i . "), ',', -1) = " . $userId . " ";
            }
          }
          $sql .= ") ";
        }
      }
      if (intval($_SESSION['adopt']) == 2) {
        $sql .= "AND `parent` != '' AND `parent` != '0' ";
      } else if (intval($_SESSION['adopt']) == 3) {
        $sql .= "AND (`parent` = '' OR `parent` = '0') ";
      }
    }
    if (isset($_SESSION['rates'])) {
      $sql .= "AND `rate` IN (" . $_SESSION['rates'] . ") ";
    }
    // Don't fetch any dead tree.
    $sql .= "AND `dead` = 0 ";

    // show recently added tree always without being affected by filtering.
    if (isset($_SESSION['temp_trees']) && $_SESSION['temp_trees'] != null) {
      $sql .= "OR `id` IN (" . $_SESSION['temp_trees'] . ") ";
    }

    // Only manager level can fetch Doghead farm.
    if ($check) {
      $sql .= "OR `id` = -1 ";
    } else {
      $sql .= "AND `id` != -1 ";
    }



    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_OBJ);
      $pdo = null;
      $params = array(
        "code" => 200,
        "trees" => $result,
      );
      echo json_encode($params);
    } catch(PDOException $e) {
      $json = array(
        "code" => $e->getCode(),
        "message" => $e->getMessage(),
      );
      echo json_encode($json);
    }
  }
?>
