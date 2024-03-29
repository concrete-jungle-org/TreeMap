<?php
  include_once 'database.php';
  include_once 'TreeMapDatabase.php';

  function debug_error($err, $banner = '') {
    if (!empty($banner)) {
      error_log("::".$banner."::");
    }
    if (is_object($err)) {
      if (method_exists($err, 'getMessage')) {
        error_log($err->getMessage());
      } else {
        error_log($err);
      }
      if (method_exists($err, 'getFile') && method_exists($err, 'getLine')) {
        error_log('From: '.$err->getFile().' on line: '.$err->getLine());
      }
      return;
    }
    if (is_array($err)) {
      error_log(print_r($err, true));
      return;
    }
    if (is_string($err)) {
      error_log($err);
      return;
    }
    error_log(json_encode($err));
  }
  function print_error($err) {
    /* file_put_contents('php://stderr', $text . PHP_EOL); */
    error_log($err);
  }

  function sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name
    $secure = true;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
      header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
      exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(3600 * 24 * 7,
      $cookieParams["path"],
      $cookieParams["domain"],
      $secure,
      $httponly);
    // Sets the session name to the one set above.
    //session_name($session_name);
    session_start();            // Start the PHP session
    //session_regenerate_id(true);    // regenerated the session, delete the old one.
    //echo session_id().'||';
  }

  function sec_session_continue() {
    $session_name = 'sec_session_id';   // Set a custom session name
    //session_name($session_name);
    session_start();            // Start the PHP session
    //echo session_id().'||';
  }

  function login($contact, $password) {
    $sql = "SELECT `id`, `auth`, `contact`, `password`, `salt` FROM `person` WHERE (`contact` = :contact)";
    $params = array(
      "contact" => $contact,
    );

    try {
      $db = new TreeMapDatabase(); 
      $pdo = $db->getConnection();
      $stmt = $pdo->prepare($sql);
      if ($stmt) {
        $stmt->execute($params);
        $result = $stmt->fetch();
        $id = $result["id"];
        $auth = $result["auth"];
        $contact = $result["contact"];
        $db_password = $result["password"];
        $salt = $result["salt"];
        // hash the password with the unique salt.
        $password = hash('sha512', $password . $salt);
        // Check if the password in the database matches the password the user submitted.
        if ($db_password == $password) {
          // Password is correct!
          // Get the user-agent string of the user.
          //$user_browser = $_SERVER['HTTP_USER_AGENT'];
          $user_browser = "HTTP_USER_AGENT";
          // XSS protection as we might print this value
          $id = preg_replace("/[^0-9]+/", "", $id);
          $_SESSION['user_id'] = $id;
          $_SESSION['user_auth'] = $auth;
          // XSS protection as we might print this value
          $contact = preg_replace("/[^a-zA-Z0-9_\-]+.@/", "", $contact);
          $_SESSION['contact'] = $contact;
          $_SESSION['login_string'] = hash('sha512', $password . $user_browser);
          // Login successful.
          return true;
        } else {
          // Password is not correct
          return false;
        }
      }
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function login_check() {
    // Check if all session variables are set
    if (isset($_SESSION['user_id'], $_SESSION['contact'], $_SESSION['login_string'])) {
      $user_id = $_SESSION['user_id'];
      $login_string = $_SESSION['login_string'];
      $username = $_SESSION['contact'];
      // Get the user-agent string of the user.
      //$user_browser = $_SERVER['HTTP_USER_AGENT'];
      $user_browser = "HTTP_USER_AGENT";

      $sql = "SELECT `password` FROM `person` WHERE (`id` = :id)";
      $params = array(
        "id" => $user_id,
      );

      try {
        $db = new TreeMapDatabase(); 
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare($sql);
        if ($stmt) {
          $stmt->execute($params);
          $result = $stmt->fetch();
          if ($stmt->rowCount() == 1) {
            // If the user exists get variables from result.
            $password = $result["password"];
            $login_check = hash('sha512', $password . $user_browser);
            if ($login_check == $login_string) {
              // Logged In!!!!
              return true;
            } else {
              // Not logged in
              return false;
            }
          } else {
            // Not logged in
            return false;
          }
        } else {
          // Not logged in
          return false;
        }
      } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        // Not logged in
        return false;
      }
    } else {
      // Not logged in
      return false;
    }
  }

  function admin_check() {
    // Check if all session variables are set
    if (isset($_SESSION['user_id'], $_SESSION['contact'], $_SESSION['login_string'], $_SESSION['user_auth'])) {
      $user_id = $_SESSION['user_id'];
      $login_string = $_SESSION['login_string'];
      $username = $_SESSION['contact'];
      $auth = $_SESSION['user_auth'];
      // Get the user-agent string of the user.
      //$user_browser = $_SERVER['HTTP_USER_AGENT'];
      $user_browser = "HTTP_USER_AGENT";

      $sql = "SELECT `password` FROM `person` WHERE (`id` = :id)";
      $params = array(
          "id" => $user_id,
      );

      try {
        $db = new TreeMapDatabase(); 
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare($sql);
        if ($stmt) {
          $stmt->execute($params);
          $result = $stmt->fetch();
          // If the user exists get variables from result.
          $password = $result["password"];
          $login_check = hash('sha512', $password . $user_browser);
          if ($login_check == $login_string) {
            // Logged In!!!!
            if ($auth == 1 || $auth == 2) {
              // Admin account
              return true;
            } else {
              return false;
            }
          } else {
            // Not logged in
            return false;
          }
        } else {
          // Not logged in
          return false;
        }
      } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        // Not logged in
        return false;
      }
    } else {
      // Not logged in
      return false;
    }
  }

  function esc_url($url) {
    if ('' == $url) {
      return $url;
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;

    $count = 1;
    while ($count) {
      $url = str_replace($strip, '', $url, $count);
    }

    $url = str_replace(';//', '://', $url);

    $url = htmlentities($url);

    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
      // We're only interested in relative links from $_SERVER['PHP_SELF']
      return '';
    } else {
      return $url;
    }
  }

  function get_foods_in_season($weeks = []) {
    $weekList = implode(', ', $weeks);
    $sql = <<<SQL
      SELECT DISTINCT food_id FROM in_season 
      WHERE week_of_year IN ($weekList);
    SQL;
    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll();
      $pdo = null;
      $foods = []; // ids for foods that are in season
      foreach ($result as $food) {
        array_push($foods, $food['food_id']);
      }
      return $foods;
    } catch(PDOException $e) {
      throw new Exception('{"error":{"text":'. $e->getMessage() .'}}');
    }
  }

  function nextWeek() {
    return date('W', strtotime('+1 week'));
  }
  function prevWeek() {
    return date('W', strtotime('-1 week'));
  }
  function currentWeek() {
    return date("W"); //current week of year ie: 1..52, sometimes 53
  }

  function calcSeasonFoods($weeks = []) {
    if (empty($weeks)) {
      $weeks = [currentWeek()];
    }
    return get_foods_in_season($weeks);
  }

  function getDefaultFlags() {
    $sql = "SELECT id FROM `flag` WHERE `filter` = 1";
    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll();
      $pdo = null;
      $flags = [0];
      foreach ($result as $flag) {
        array_push($flags, $flag["id"]);
      }
      return implode(',', $flags);
    } catch(PDOException $e) {
      return '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function getAllFlags() {
    $sql = "SELECT id FROM `flag`";
    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll();
      $pdo = null;
      $flags = [];
      foreach ($result as $flag) {
        array_push($flags, $flag["id"]);
      }
      return implode(',', $flags);
    } catch(PDOException $e) {
      return '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function getFlagSize() {
    $sql = "SELECT id FROM `flag`";
    try {
      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll();
      $pdo = null;
      $size = 0;
      foreach ($result as $flag) {
        $size++;
      }
      return $size;
    } catch(PDOException $e) {
      return '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

?>
