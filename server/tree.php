<?php
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");



  include_once 'functions.php';
  include_once 'treeRow.php';
  include_once 'treeDB.php';
  sec_session_continue(); // Our custom secure way of starting a PHP session.

  switch($_SERVER['REQUEST_METHOD']){
    case 'POST':
      create();
      break;
    case 'GET':
      read();
      break;
    case 'PUT':
      update();
      break;
    case 'DELETE':
      delete();
      break;
  }

  function read() {
    $data = json_decode(file_get_contents('php://input'));
    $params = null;
    if ($data != null) {
      $params = array(
      "id" => $data->{'id'},
      );
    } else {
      $params = array(
        "id" => $_GET['id'],
      );
    }
    $check = admin_check();
    if (!$check) {
      $public = "1";
      $dead = "0";
    } else {
      $public = "0,1";
      $dead = "0,1";
    }
    $sql = "SELECT * FROM `tree` WHERE (`id` = :id)";
    $sql .= "AND `public` IN (".$public.") ";
    $sql .= "AND `dead` IN (".$dead.") ";

    // Only manager level can fetch Doghead farm.
    if (!$check) {
      $sql .= "AND `id` != -1 ";
    }
    // Don't fetch any dead tree.
    $sql .= "AND `dead` = 0 ";

    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_CLASS, 'TreeRow');
      $stmt->execute($params);
      $result = $stmt->fetch();
      $pdo = null;
      $json = array(
        "code" => 200,
        "tree" => $result,
      );
      echo json_encode($json);
    } catch(PDOException $e) {
      $json = array(
        "code" => $e->getCode(),
        "message" => $e->getMessage(),
      );
      echo json_encode($json);
    }
  }

  function update() {
    $data = json_decode(file_get_contents('php://input'));
    $params = null;
    if ($data != null) {
      $params = array(
        "id" => $data->{'id'},
        "lat" => $data->{'lat'},
        "lng" => $data->{'lng'},
        "food" => $data->{'food'},
        "description" => $data->{'description'},
        "address" => $data->{'address'},
        "public" => $data->{'public'},
        "dead" => $data->{'dead'},
        "owner" => $data->{'owner'},
        "parent" => $data->{'parent'},
        "rate" => $data->{'rate'},
        "updated" => date("Y-m-d H:i:s"),
      );
    }
    $sql = "UPDATE `tree` SET `lat` = :lat, `lng` = :lng, `food` = :food, `public` = :public, `dead` = :dead, `parent` = :parent, `rate` = :rate, `owner` = :owner, `description` = :description, `address` = :address, `updated` = :updated WHERE (`id` = :id)";

    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);

      $sql = "SELECT * FROM `tree` WHERE (`id` = :id)";
      $params = array(
        "id" => $data->{'id'},
      );

      try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $pdo = null;
        $params = array(
          "code" => 200,
          "tree" => $result[0],
        );
        echo json_encode($params);
      } catch(PDOException $e) {
        $json = array(
          "code" => $e->getCode(),
          "message" => $e->getMessage(),
        );
        echo json_encode($json);
      }

    } catch(PDOException $e) {
      $json = array(
        "code" => $e->getCode(),
        "message" => $e->getMessage(),
      );
      echo json_encode($json);
    }
  }

  function create() {
    $data = json_decode(file_get_contents('php://input'));
    $owner = "0";
    if (isset($_SESSION['user_id'])) {
      $owner = $_SESSION['user_id'];
    }

    try {
      $treeDB = new TreeDatabase();
      $newTree = $treeDB->create((array) $data);
      $params = array(
        "code" => 200,
        "tree" => $newTree,
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

  function delete() {
    $data = json_decode(file_get_contents('php://input'));
    $params = array(
        "id" => $data->{'id'},
    );
    $check = admin_check();
    if (isset($_SESSION['temp_trees']) && $_SESSION['temp_trees'] != null) {
      $temp_trees = explode(",", $_SESSION['temp_trees']);
      $check = in_array($params['id'], $temp_trees);
    }

    if ($check) {

      $sql = "DELETE FROM `tree` WHERE (`id` = :id)";
      try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        $pdo = null;
        $params = array(
          "code" => 200,
          "tree" => $result,
        );
        echo json_encode($params);
      } catch(PDOException $e) {
        $json = array(
          "code" => $e->getCode(),
          "message" => $e->getMessage(),
        );
        echo json_encode($json);
      }
    } else {
      $json = array(
        "code" => 901,
        "message" => "Access is not authorized.",
      );
      echo json_encode($json);
    }
  }
?>
