<?php
  require '../vendor/autoload.php';
  include_once 'treeRow.php';
  include_once 'database.php';

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
    
    public function update($table, $recordId) {
      //NOTE: this method call airtable API which is rate limited to 5 calls per second
      $record = $this->airtable->get($table, $recordId);
      $sqliteFields = $this->convertAirtableToSQLite($record);
      $sqlite_record = $this->upsert($sqliteFields);
      return $sqlite_record;
    }


    public function convertTreeRecordToAirtableRecord(array $data): Array {
      return [
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
    }

    public function convertAirtableToSQLite(\Guym4c\Airtable\Record $record): Array {
      $airtableData = $record->getData();
      error_log('AT_DATA:..'.json_encode($airtableData));
      return [
        "id" => $airtableData["id"],
        "airtable_id" => $record->getId(),
        "lat" => $airtableData["lat"],
        "lng" => $airtableData["lng"],
        "food" => json_encode($airtableData["food"]), // food is saved as a json_encoded array, and converted back to simple string when read
        "owner" => $airtableData["owner"] ?? 111, //NOTE: when null, this triggers a warning in error_log: PHP Warning:  Undefined array key "owner"
        "description" => $airtableData["description"],
        "address" => $airtableData["full address"],
        "public" => $airtableData["public"],
        "dead" => $airtableData["dead"] ?? 0,
        "parent" => $airtableData["parent"] ?? 0,
        "rate" => $airtableData["rate"] ?? 0,
        "created_date" => $airtableData["created date"],
        "updated" => $airtableData["created date"]
      ];
    }

    public function upsert(array $sqliteFields): TreeRow {
      // SQLite record is updated or created
      // Save only the columns needed by the app to local sqlite db
      // Values are matched with columns based on position
      $sql = "INSERT OR REPLACE INTO `tree` 
                     ( id, airtable_id, lat, lng, food, owner, description, [full address], public, dead, parent, rate, [created date], updated)
              VALUES (:id,:airtable_id,:lat,:lng,:food,:owner,:description, :address,      :public,:dead,:parent,:rate, :created_date, :updated)";

      $pdo = getConnection();
      $stmt = $pdo->prepare($sql);
      $stmt->execute($sqliteFields);

      // Fetch the newly created or updated tree
      $sql = "SELECT * FROM `tree` WHERE `id` = :id";
      $params = [ "id" => $sqliteFields["id"] ];

      $stmt = $pdo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_CLASS, 'TreeRow');
      $stmt->execute($params);
      $result = $stmt->fetch();
      $pdo = null;
      return $result;
    }

    public function delete($airtableId) {
      $sql = "DELETE FROM `tree` WHERE `airtable_id` = :id";
      $params = [ "id" => $airtableId ];

      try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pdo = null;
      } catch(PDOException $e) {
        print_error("Unable to delete from tree");
        print_error($e->getMessage());
      }
    }

    public function create(array $data): TreeRow {
      $airtableFields = $this->convertTreeRecordToAirtableRecord($data);
      // Slow operation, occassionally times out after 5 seconds
      $record = $this->airtable->create($this->table, $airtableFields);

      $sqliteFields = $this->convertAirtableToSQLite($record);

      // Store newly added tree into a cookie so that users can edit before cookie being expired.
      if (isset($_SESSION['temp_trees']) && $_SESSION['temp_trees'] != null) {
        $temp_trees = explode(",", $_SESSION['temp_trees']);
        array_push($temp_trees, $sqliteFields['id']);
        $_SESSION['temp_trees'] = implode(',', $temp_trees);
      } else {
        $_SESSION['temp_trees'] = $sqliteFields['id'];
      }
      $_SESSION['LAST_CREATE'] = $_SERVER['REQUEST_TIME'];

      return $this->upsert($sqliteFields);
    }
  }
?>

