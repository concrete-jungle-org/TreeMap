<?php
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  class Tree {
   public $id_number;
   public $id;
   public $airtable_id;
   public $lat;
   public $lng;
   /* public $food; */
   public $owner;
   public $description;
   public $full;
   public $public;
   public $dead;
   public $parent;
   public $updated;
   public $rate;
   /* public $Geocode cache; */
   /* public $Fruit (for mapping); */
   /* public $lat, lng; */
   /* public $Today's Date; */
   /* public $Fruit; */
   /* public $Street Address; */
   /* public $Tree id; */ //an odd field that concatenates many properties
   /* public $Log Notes; */
   /* public $created date; */
   /* public $Public or Private Property; */
   /* public $Airtable Last Updated; */
   /* public $Days b/w most recent scout and first/next scout day; */
   /* public $Days b/w most recent scout and today; */
   /* public $Concrete Jungle City; */
   /* public $airtable_createdTime; */
   /* public $Note; */
   /* public $Atlanta or Athens; */
   /* public $Comments and Notes (Raw Text); */
   /* public $Scout Notes; */
   /* public $Permission to Pick- 2021; */
   /* public $Most recent scout date; */
   /* public $Note Group; */
   /* public $Scout (from Note); */

    public function __set($name, $value) {
      if ($name == 'Stree Address') {
        $this->address = $value;
      }
      if ($name == 'food') { 
        //transform: ["recABC"] to "recABC"
        $foodArray = json_decode($value);
        $this->food = $foodArray[0];
      }
    }
  }

?>
