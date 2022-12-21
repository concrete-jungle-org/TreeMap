<?php
  require '../vendor/autoload.php';
  include_once 'treeRow.php';

  // An object to contain knowledge of airtable api and sqlite
  class TreeDatabase {
    private $airtable;
    private $table;

    public function __construct() {
      $key   = $_ENV['AT_AUTH_TOKEN'];
      $base  = $_ENV['AT_BASE_ID'];
      $airtableClient = new \Guym4c\Airtable\Airtable($key, $base);
      $table = $_ENV['AT_TABLE_TREE'];
      $this->table = $table;
      $this->airtable = $airtableClient;
    }
    
    public function create(array $data): TreeRow {
      // Whitelist of column names that can be set upon creation
      $airtableFields = [
          "lat" => $data["lat"],
          "lng" => $data["lng"],
          "food" => (array) $data["food"],
          "owner" => strval($data["owner"]),
          "description" => $data["description"],
          "full address" => $data["address"],
          "public" => strval($data["public"]),
          "dead" => strval($data["dead"]),
          "parent" => intval($data["parent"]),
          "rate" => intval($data["rate"])
      ];

      // Slow operation, occassionally times out after 5 seconds
      $record = $this->airtable->create($this->table, $airtableFields);

      $airtableData = $record->getData();

      // Save only the columns needed by the app to local sqlite db
      // Values are matched with columns based on position
      $sql = "INSERT INTO `tree` (id, airtable_id, lat, lng, food, owner, description, [full address], public, dead, parent, rate, [created date], updated)
                         VALUES (:id,:airtable_id,:lat,:lng,:food,:owner,:description, :address,      :public,:dead,:parent,:rate, :created_date, :updated )";

      $sqliteFields = [
        "id" => $airtableData["id"],
        "airtable_id" => $record->getId(),
        "lat" => $airtableData["lat"],
        "lng" => $airtableData["lng"],
        "food" => json_encode($airtableData["food"]), // food is saved as a json_encoded array, and converted back to simple string when read
        "owner" => $airtableData["owner"],
        "description" => $airtableData["description"],
        "address" => $airtableData["full address"],
        "public" => $airtableData["public"],
        "dead" => $airtableData["dead"],
        "parent" => $airtableData["parent"],
        "rate" => $airtableData["rate"],
        "created_date" => $airtableData["created date"],
        "updated" => $airtableData["created date"]
      ];

      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute($sqliteFields);

      $sql = "SELECT * FROM `tree` WHERE `id` = :id";
      $params = [ "id" => $airtableData["id"] ];

      // Store newly added tree into a cookie so that users can edit before cookie being expired.
      if (isset($_SESSION['temp_trees']) && $_SESSION['temp_trees'] != null) {
        $temp_trees = explode(",", $_SESSION['temp_trees']);
        array_push($temp_trees, $params['id']);
        $_SESSION['temp_trees'] = implode(',', $temp_trees);
      } else {
        $_SESSION['temp_trees'] = $params['id'];
      }
      $_SESSION['LAST_CREATE'] = $_SERVER['REQUEST_TIME'];

      $stmt = $pdo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_CLASS, 'TreeRow');
      $stmt->execute($params);
      $result = $stmt->fetch();
      $pdo = null;
      return $result;
    }
  }
?>


