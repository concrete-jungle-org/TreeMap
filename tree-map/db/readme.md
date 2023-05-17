# The Database

## Background
The app originally was called FoodParent2.0 and worked with a mysql database, 
then it was transitioned to a sqlite db that was essentially a dump of the mysql
data and finally transitioned again to a sqlite db that is almost identical to 
the airtable database. Some minor chanes included in the alter table statements
included here. The final sqlite db is basically a cache that gets renewed every 
24 hrs or so from a backup of airtable.

The app was renamed from FoodParent or FoodMap to TreeMap, though some old
references may still exist.

Whenever setting up the app to run in a new environment, the database files it 
depends on also need to be present. For the staging and production environments 
the db file already exists, for your local development you will need to copy 
these files. 

You can ssh into the server and copy the sqlite files needed back into this 
directory, where they will be ignored by git if the file ends with `.sqlite`.

## Directory Contents:

- `airtable.sqlite`: an export of the data from airtable
- `alter_xxx`: sqlite scripts to be executed against an airtable backup found in the [cj-airtable repo](https://github.com/concrete-jungle-org/cj-airtable).
- `treemap.sqlite`: a database for app specific data not related to airtable data, For example the cursor position of the airtable webhooks.

## Dev Tips

To get started: 
- Copy over the 2 db sqlite files from staging to your local dev env.
- Install sqlite3 and verify access to sqlite database: `pragma table_info(food)`


### Airtable

The goal is to get the app to behave the same as production using this database: airtable.sqlite.
However the database schema used in airtable differs from the original mysql db.
The cj-airtable repo creates nightly backups using this tool [airtable-export-tool](https://datasette.io/tools/airtable-export)
You can setup and run this tool locally if you want, instructions are given on the site.
The tool has not been updated since Apr 9, 2021, as of Apr 2023 it still works.

### Sqlite

This database was created with this python tool after the mysql dump file was turned into a full mysql db with a local user.

  - `pip install mysql-to-sqlite3`

```
mysql2sqlite --sqlite-file tree_parent.sqlite --mysql-database tree_parent --mysql-user <your_user> --prompt-mysql-password
```

In order to calculate in-season food without performing a full table update on foods, a new In_Season table was created. 
This new table simply reads from the Donate table to generate a simple schema showing when foods were ready for donation
in past years. The In_Season table is basically just view of the Donate table, and since the Donate table no longer
receives any udpates, neither does the In_Season table. Since the data is not dynamic, there is no reason to create a
table in airtable, and so a script was created to run every time a new airtable cache is downloaded.

This is preferable to a real In_Season table in the production db because cloning a db from prod to staging generates
new record ids, or otherwise breaks the link, so that the food_id column points non-existent ids in the food table.
So then the table is would have to be regenerated from the Donate table and the results imported into airtable's In_Season
table after every db refresh. An unneccessary hassle that is avoided by using the `create_in_season_tbl.sql` script.

 
### MySQL (Legacy)

When you want to work with the app as it was in production follow the [Dev_Setup.md](../../docs/Dev_Setup.md) instructions.
The original app required a dbpass.php and a database.php file. The database file is here with a `.mysql` extension.
The .mysql extension was added to avoid confusion with other forms of this file elsewhere in this repo, but gets
removed when using. The [dbpass](dbpass.php) contains sensitive info and therefore is stored on the server not the repo.

`dbpass.php`: Not included, this file has the pw required to connect to mysql db. Stored on nfshost server only. Used if reverting the app to old production-like state
`database.php.mysql`: Not included, contains the db connection code used if reverting the app to old production-like state. Available in git history prior to April 13th, 2023

Install Mysql locally:
- How I setup local devlopment with a full mysql db to get this working
  - `brew install mysql@5.7`
  - `brew link --force mysql@5.7`
  - `mysql_secure_installation`
  - `mysql -u root -p`
  - GRANT CREATE, ALTER, DROP, INSERT, UPDATE, DELETE, SELECT, REFERENCES, RELOAD on *.* TO 'my_user'@'localhost' WITH GRANT OPTION;
  - SHOW GRANTS FOR 'my_user'@'localhost';
  - CREATE DATABASE tree_parent
  - `head -n 5 tree_parent.dump`
  - `mysql -u username -p tree_parent < tree_parent.dump`
  - login and > USE tree_parent; SHOW TABLES;

Legacy MySQL dev:
- I installed mysql ver 14, dist 5.7 and set up a local copy from the mysql dump that I could access from the CLI.
- I am using DBeaver with all three databases loaded as sources, this way I can:
  - test the original query against mysql to see the expected results
  - test a modified query against tree_parent.sqlite to get the SQL conversion working, returns expected results
  - test the updated sqlite query against the airtable.sqlite db, to get expected results
  - NOTE: this worked well with one exception concerning what food is in season, more on that later.

