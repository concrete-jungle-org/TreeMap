<?php
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->required(['AT_TABLE_TREE'])->notEmpty();
$dotenv->load();

include_once 'TreeMapDatabase.php';
include_once 'TreeRepository.php';

// Jobs can be for any table type
// currently only subscribed to changes on tree table
// so there only exist jobs for TreeRepository
abstract class AirtableJob {
  protected $payloadsId;
  protected $baseTx;
  protected $tableId;
  protected $recordId;
  protected $status;
  protected $timestamp;
  protected $format;
  protected $repository;
  protected $db;

  public function __construct($baseTx, $recordId, $tableId, $payloadsId, $timestamp) {
    $this->baseTx = $baseTx;
    $this->recordId = $recordId;
    $this->tableId = $tableId;
    $this->payloadsId = $payloadsId;
    $this->timestamp = $timestamp;
    $this->status = 'pending';
    $this->repository = $this->getRepository($tableId);
    $this->db = new TreeMapDatabase();
  }
  abstract public function execute();
  abstract public function getDescription();
  
  private function getRepository($tableId) {
    if ($_ENV['AT_TABLE_TREE'] == $tableId) {
      return new TreeRepository();
    }
    debug_error('Unable to match tableId to a Repository for: '.$tableId);
  }

  public function log() {
    $sql = <<<SQL
      INSERT INTO airtable_job (payloads_id, description, timestamp, status) 
      VALUES ( :payloads_id, :description, :timestamp, :status);
    SQL;
    $params = [
      "payloads_id" => $this->payloadsId, 
      "description" => $this->getDescription(),
      "timestamp" => $this->timestamp,
      "status" => $this->status,
    ];
    try {
      $this->db->query($sql, $params);
    } catch (Exception $err) {
      debug_error($err, 'Unable to log airtable job.');
    }
  }
}

final class AirtableInsert extends AirtableJob {

  public function execute(): string {
    try {
      $this->repository->refresh($this->recordId);
      $this->status = 'success';
    } catch (Exception $err) {
      debug_error($err);
      $this->status = 'failure';
    }
    return $this->status;
  }
  public function getDescription() {
    return "insert ".$this->recordId.", ".$this->tableId;
  }
  public function __toString() {
    return $this->getDescription();
  }
}

final class AirtableUpdate extends AirtableJob {
  public function execute(): string {
    try {
      $this->repository->refresh($this->recordId);
      $this->status = 'success';
    } catch (Exception $err) {
      debug_error($err);
      $this->status = 'failure';
    }
    return $this->status;
  }
  public function getDescription() {
    return "update ".$this->recordId.", ".$this->tableId;
  }
  public function __toString() {
    return $this->getDescription();
  }
}

final class AirtableDelete extends AirtableJob {
  public function execute(): string {
    try {
      $this->repository->delete($this->recordId);
      $this->status = 'success';
    } catch (Exception $err) {
      debug_error($err);
      $this->status = 'failure';
    }
    return $this->status;
  }
  public function getDescription() {
    return "delete ".$this->recordId.", ".$this->tableId;
  }
  public function __toString() {
    return $this->getDescription();
  }
}
?>
