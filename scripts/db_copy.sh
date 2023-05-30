#! /bin/sh
set -eu

DB_PATH=/home/public/tree-map/db

mv $DB_PATH/airtable.sqlite $DB_PATH/airtable_old.sqlite
mv $DB_PATH/tree_parent.db $DB_PATH/airtable.sqlite
chmod g+w /home/public/tree-map
chmod g+w /home/public/tree-map/db
chgrp web $DB_PATH/airtable.sqlite
chmod 664 $DB_PATH/airtable.sqlite
sqlite3 $DB_PATH/airtable.sqlite < $DB_PATH/alter_food_col.sql
sqlite3 $DB_PATH/airtable.sqlite < $DB_PATH/create_in_season_tbl.sql
