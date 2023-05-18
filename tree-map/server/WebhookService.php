<?php
  require '../vendor/autoload.php';
  include_once 'functions.php';
  include_once 'AirtableJobs.php';
  include_once 'TreeMapDatabase.php';

  class AirtableJobsFactory {
    public $payloads;
    private $id;
    private $jobs;
  
    public function __construct($data, $payloadsId) {
      $this->payloads = $data['payloads'] ?? array();
      $this->id = $payloadsId;
    }
    public function createJobs() {
      //Each payload may become more than one job, common for deletes
      //The recordId is assumed to be unique across tables, to determine duplicates
      foreach ($this->payloads as $payload) {
        $timestamp = $payload['timestamp'];
        $baseTx = $payload['baseTransactionNumber'];
        $changes = $payload['changedTablesById'];
        foreach ($changes as $tableId => $table) {
          $createIds = array_keys($table['createdRecordsById'] ?? array());
          $changeIds = array_keys($table['changedRecordsById'] ?? array());
          $destroyIds = $table['destroyedRecordIds'] ?? array(); //TEST
          // Order matters, later jobs overwrite earlier ones
          foreach ($changeIds as $recordId) {
            $this->jobs[$recordId] = new AirtableUpdate($baseTx, $recordId, $tableId, $this->id, $timestamp);
          }
          foreach ($createIds as $recordId) {
            $this->jobs[$recordId] = new AirtableInsert($baseTx, $recordId, $tableId, $this->id, $timestamp);
          }
          foreach ($destroyIds as $recordId) {
            $this->jobs[$recordId] = new AirtableDelete($baseTx, $recordId, $tableId, $this->id, $timestamp);
          }
        }
      }
      
      return $this->jobs;
    }
  }


  class WebhookService {
    public $webhook_id = 0;
    private $db;
    private $api;

    public function __construct($data, $persistence, $api) {
      $this->webhook_id = $data->webhook->id; //$data also includes base->id and timestamp
      $this->db = $persistence;
      $this->api = $api;
    }
    public function executePayload() {
      $payloads = $this->fetchPayloads();
      $jobs = $payloads->createJobs() ?? array();
      foreach ($jobs as $recordId => $job) {
        $status = $job->execute();
        $job->log();
      }
    }
    public function fetchPayloads() {
      $id = $this->webhook_id;
      $cursor = $this->getCursor($id);
      $url = sprintf("webhooks/%s/payloads?cursor=%d", $id, $cursor);
      try {
        $payloads = $this->api->fetch($url);
      } catch (PDOException $e) {
        debug_error($e, "ERROR Unable to fetch webhook payloads");
      }
      $rowId = $this->setCursor($payloads['cursor'], $id);
      return new AirtableJobsFactory($payloads, $rowId);
    }
    private function getCursor($webhook_id) {
      $sql = <<<SQL
        SELECT cursor FROM airtable_payloads
        WHERE webhook_id = :webhook_id ORDER BY cursor DESC LIMIT 1;
      SQL;
      $params = ['webhook_id' => $webhook_id];
      try {
        $result = $this->db->query($sql, $params);      
        $cursor = $result['cursor'];
        if (is_null($cursor)) {
          $cursor = 1;
        }
      } catch(PDOException $e) {
        debug_error($e, "ERROR Unable to get cursor position from database");
      }
      return $cursor;
    }
    private function setCursor($cursor, $webhook_id) {
      $sql = <<<SQL
        INSERT INTO airtable_payloads (cursor, webhook_id) 
        VALUES ( :cursor, :webhook_id );
      SQL;
      $params = ["cursor" => $cursor, "webhook_id" => $webhook_id];

      $rowId = 0;
      try {
        $this->db->query($sql, $params);      
        $rowId = $this->db->lastInsertRowId('airtable_payloads');
      } catch(PDOException $e) {
        debug_error($e, "ERROR Unable to insert cursor into database");
      }
      return $rowId;
    }

    public function verify($hash) {
    // A hash of the body contents using the hook's MAC secret
    // is given in the X-Airtable-Content-MAC header. 
    // The recipient can verify this value
    // Note: Use the decoded macSecret here, not the Base64-encoded
    // version that was returned from the webhook create API action.
    /*
        const hmac = require('crypto').createHmac('sha256', macSecret);
        hmac.update(requestBody.toString(), 'ascii');
        const expectedContentHmac = 'hmac-sha256=' + hmac.digest('hex');
    */
    }
  }
    
?>
