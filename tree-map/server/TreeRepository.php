<?php
  require '../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->required(['AT_TABLE_TREE'])->notEmpty();
  $dotenv->load();
  include_once 'AirtableDatabase.php';
  include_once 'AirtableCache.php';
  include_once 'treeRow.php';

  class TreeRepository {
    private $db;
    private $table;
    private $cache;

    // todo: pass arguments for the db and cache
    public function __construct() {
      $this->table = $_ENV['AT_TABLE_TREE'];
      $this->db = AirtableDatabase::initialize();
      $this->cache = new AirtableCache();
    }
    
    public function refresh($recordId) {
      //NOTE: this method call airtable API which is rate limited to 5 calls per second
      try {
        $record = $this->db->get($this->table, $recordId);
      } catch (Exception $e) {
        debug_error($e, 'Unable to get from Airtable record: '.$recordId);
        throw $e;
      }
      $sqliteFields = $this->convertAirtableToSQLite($record);
      try {
        $sqlite_record = $this->upsert($sqliteFields);
      } catch (Exception $e) {
        debug_error($e, 'Unable to upsert to Cache record: '.$recordId);
        throw $e;
      }
      return $sqlite_record; //should I return a TreeModel?
    }


    private function convertTreeRecordToAirtableRecord(array $data): Array {
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

    private function convertAirtableToSQLite(\Guym4c\Airtable\Record $record): Array {
      $airtableData = $record->getData();
      return [
        "id" => $airtableData["id"],
        "airtable_id" => $record->getId(),
        "lat" => $airtableData["lat"],
        "lng" => $airtableData["lng"],
        "food" => json_encode($airtableData["food"]), // food is saved as a json_encoded array, and converted back to simple string when read
        "owner" => $airtableData["owner"] ?? 0, //NOTE: when null, this triggers a warning in error_log: PHP Warning:  Undefined array key "owner"
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
      $sql = <<<SQL
        INSERT OR REPLACE INTO tree
               ( id, airtable_id, lat, lng, food, owner, description, [full address], public, dead, parent, rate, [created date], updated)
        VALUES (:id,:airtable_id,:lat,:lng,:food,:owner,:description, :address,      :public,:dead,:parent,:rate, :created_date, :updated);
      SQL;

      $this->cache->query($sql, $sqliteFields);

      // Fetch the newly created or updated tree
      $sql = "SELECT * FROM tree WHERE id = :id";
      $params = [ "id" => $sqliteFields["id"] ];

      $result = $this->cache->query($sql, $params, 'TreeRow');
      return $result;
    }

    //todo: rename removeFromCache
    public function delete($airtableId) {
      $sql = "DELETE FROM tree WHERE airtable_id = :id";
      $params = [ "id" => $airtableId ];

      try {
        $this->cache->query($sql, $params);
      } catch(PDOException $e) {
        print_error("Unable to delete from tree");
        print_error($e->getMessage());
      }
    }

    public function create(array $data): TreeRow {
      $airtableFields = $this->convertTreeRecordToAirtableRecord($data);
      // Slow operation, occassionally times out after 5 seconds
      $record = $this->db->create($this->table, $airtableFields);

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

