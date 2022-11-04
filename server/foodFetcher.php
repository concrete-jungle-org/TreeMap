<?php
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  class Food {
    public $id; // alter table stmt changed airtable_id to id, and id to old_id
    public $id_number;
    public $name;
    public $season;
    public $adopt;
    public $farm;
    public $airtable_createdTime;
    public $updated;
    public $Donate;
    public $Tree;

    public function __set($name, $value) {
      // Any field not explicitly listed on the Food object
      // will be processed here to either get assigned a new value
      // or be silently ignored
      if ($name == 'Food id #') {
        $this->id_number = $value;
      }
      if ($name == 'icon') {
        $iconArray = json_decode($value);
        if ($iconArray) {
          $iconFile = $iconArray[0]->filename;
          $this->icon = $iconFile;
        } else {
          $this->icon = '';
        }
      }
    }
  }
?>
