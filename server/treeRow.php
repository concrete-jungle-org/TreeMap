<?php
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  // Used to create Tree objects from the sqlite database with setFetchMode(PDO::FETCH_CLASS, 'TreeRow');
  // No PDO fecth mode for a class that accepts values in the constructor, so no constructor needed.
  #[AllowDynamicProperties] // suppresses a deprecation warning in PHPv8.2 caused by adding a prop in __set method
  class TreeRow {
    public $id;
    public $airtable_id;
    public $lat;
    public $lng;
    public $owner;
    public $description;
    public $public;
    public $dead;
    public $parent;
    public $updated;
    public $rate;

    // ALTERED DB FIELDS
    /* public $food; */
    /* public $[full address]; */
    /* public $[created date]; */

    // IGNORED DB FIELDS
    /* public $Geocode cache; */
    /* public $Fruit (for mapping); */
    /* public $lat, lng; */
    /* public $Today's Date; */
    /* public $Fruit; */
    /* public $Tree id; */ //an odd field that concatenates many properties
    /* public $Log Notes; */
    /* public $Public or Private Property; */
    /* public $Airtable Last Updated; */
    /* public $Days b/w most recent scout and first/next scout day; */
    /* public $Days b/w most recent scout and today; */
    /* public $Concrete Jungle City; */
    /* public $airtable_createdTime; */
    /* public $Note; */
    /* public $Atlanta or Athens; */
    /* public $Street Address; */
    /* public $Comments and Notes (Raw Text); */
    /* public $Scout Notes; */
    /* public $Permission to Pick- 2021; */
    /* public $Most recent scout date; */
    /* public $Note Group; */
    /* public $Scout (from Note); */

    public function __set($name, $value) {
      if ($name == 'full address') {
        $this->address = $value;
      }
      if ($name == 'food') { 
        //transform: ["recABC"] to "recABC"
        $foodArray = json_decode($value);
        if (!is_null($foodArray)) {
          $this->food = $foodArray[0];
        }
      }
      if ($name == 'created date') {
        $this->created_date = $value; 
      }
    }
  }
?>
