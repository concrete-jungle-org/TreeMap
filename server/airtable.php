<?php

  require '../vendor/autoload.php';
  include_once 'treeDB.php';

  class AirtableJob {
    public $tableId;
    public $records = array();

    public function __construct($tableId, $records) {
      $this->tableId = $tableId;
      $this->records = array_unique($records);
    }
  }

  //Assuming 1 table per payload, which has 1 or more records to be updated
  class AirtablePayload {
    private $timestamp;
    private $tx;
    private $table = ''; // TODO: dont assume only 1 table
    private $tables;
    private $records = []; //changed or created, ignore deletes
    private $deletes = [];
    private $jobs = [];
    private $jobDeletes = [];

    public function __construct(array $update) {
      $this->timestamp = $update['timestamp'];
      $this->tx = $update['baseTransactionNumber'];
      $changes = $update['changedTablesById'];
      $tables = array_keys($changes);
      $this->tables = $tables;
      //TODO: foreach tables
      $table = $tables[0]; //assuming only ever 1 table changes
      $this->table = $table; 
      $recordsChanged = $changes[$table]['changedRecordsById'] ?? array(); //may not exist
      $recordsCreated = $changes[$table]['createdRecordsById'] ?? array(); //may not exist
      $recordsDeleted = $changes[$table]['destroyedRecordIds'] ?? array(); //may not exist
      $recordsChangedKeys = array_keys($recordsChanged);
      $recordsCreatedKeys = array_keys($recordsCreated);
      $this->records = array_merge($recordsChangedKeys, $recordsCreatedKeys);
      $this->deletes = $recordsDeleted;
      $this->setJob($this->table, $this->records); 
      $this->setJobDeletes($this->table, $this->deletes);
    }

    public function getTableId() {
      return $this->table;
    }
  
    public function setJob($table, $records) {
      $this->jobs[] = new AirtableJob($table, $records);
    }

    public function setJobDeletes($table, $records) {
    //now i have to be careful to run deletes after updates
    //in case i have an insert then delete
      $this->jobDeletes[] = new AirtableJob($table, $records);
    }

    public function getJobDeletes() {
      return $this->jobDeletes;
    }
    public function getJobs() {
      return $this->jobs;
    }
  }

  class AirtableUpdate {
    private $payloads = array(); //of type payload
    private $masterJob = array(); //[{'tbl123': ['abc', 'def']}]
    private $masterDeletes = array(); //[{'tbl123': ['abc', 'def']}]
    public  $cursor; //integer position
 

    public function __construct($data) {
      $air_payloads = $data['payloads'];
      $this->cursor = $data['cursor'];
      foreach ($air_payloads as $payload) {
        $this->addPayload(new AirtablePayload($payload));
      }
      foreach ($this->payloads as $payload) {
        foreach ($payload->getJobs() as $job) {
          $this->addJob($job);
        }
        foreach ($payload->getJobDeletes() as $delete) {
          $this->addDeletes($delete);
        }
        
      }
    }

    public function addPayload($payload) {
      $this->payloads[] = $payload;
    } 

    public function addJob($job) {
      $id = $job->tableId;
      $new_records = $job->records;
      $old_records = $this->masterJob[$id] ?? [];
      $all_records = array_unique(array_merge($old_records, $new_records));
      $this->masterJob[$id] = $all_records;
    }

    public function addDeletes($job) {
      $table_id = $job->tableId;
      $new_records = $job->records;
      $old_records = $this->masterDeletes[$table_id] ?? [];
      $all_records = array_unique(array_merge($old_records, $new_records));
      $this->masterDeletes[$table_id] = $all_records;
    }

    public function updateRecords() {
      $tree_db = new TreeDatabase();
      $results = [];
      $jobs = $this->masterJob;
      $tableIds = array_keys($jobs);
      foreach ($tableIds as $tableId) {
        $recordIds = $jobs[$tableId];
        foreach ($recordIds as $recordId) {
          //TODO: needs a try/catch
          $results[] = $tree_db->update($tableId, $recordId);
        }
      }
      $d_results = [];
      $deletes = $this->masterDeletes;
      $d_tableIds = array_keys($deletes);
      foreach ($d_tableIds as $tableId) {
        $recordIds = $deletes[$tableId];
        foreach ($recordIds as $recordId) {
          //TODO: needs a try/catch
          $d_results[] = $tree_db->delete($recordId);
        }
      }
      return $results;
    }
  }

?>
