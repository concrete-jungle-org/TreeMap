# The Database

To help the transition from the apps original mysql db to an sqlite dump from airtable, I have been working with
3 databases: the original mysql, a copy of this data converted to sqlite, and a version of airtable converted to sqlite.

When developing you may find it easiest to transition the app if you can obtain these three copies and test with them.
Since the databases are large, unversionable, and contain some sensitive info like email addresses, I am not storing them in github.
They were saved in the demo server at NearlyFreeSpeech.net with: `rsync -azP ./db/ ${CJ_PROD_USER}@${CJ_PROD_HOST}:/home/public/db`

You can ssh into the server and copy the files needed back into this directory, where they will be ignored by git but still
easily available for development.

## Directory Contents:

- `tree_parent.dump`: what the app originally worked with
- `tree_parent.sqlite`: a conversion of the mysql dump file into an sqlite file
- `airtable.sqlite`: an export of the data from airtable (new schema)
- `alter_xxx`: sqlite scripts to be executed against an airtable backup found in the [cj-airtable repo](https://github.com/natomato/cj-airtable).
- `database.php.mysql`: contains the db connection code used if reverting the app to old production-like state
- `dbpass.php.mysql`: contains the password file used if reverting the app to old production-like state that uses mysql

## Dev Tips

To get started: 
- I installed mysql ver 14, dist 5.7 and set up a local copy from the mysql dump that I could access from the CLI.
- I installed sqlite3 and verified I could access the sqlite database and read table table information ie: `pragma table_info(food)`
- I am using DBeaver with all three databases loaded as source, this way I can:
  - test the original query against mysql to see the expected results
  - test a modified query against tree_parent.sqlite to get the SQL conversion working, returns expected results
  - test the updated sqlite query against the airtable.sqlite db, to get expected results
  - NOTE: this worked well with one exception concerning what food is in season, more on that later.


### Airtable

The goal is to get the app to behave the same as production using this database: airtable.sqlite.
Any modification to the database schema needs to be recorded in an alter script so it can be re-created when db updated.
The cj-airtable repo creates nightly backups via using this tool [airtable-export-tool](https://datasette.io/tools/airtable-export)
You can setup and run this tool locally if you want, instructions are given on the site.
The tool has not been updated since Apir 9, 2021, as of Aug 2022 it works perfectly.

### MySQL

When you want to work with the app as it is in production follow the [Dev_Setup.md](../docs/Dev_Setup.md) instructions.
The original app required a dbpass.php and a database.php file. They are here with a `.mysql` extension
that needs to be removed. It was added to avoid confusion with other forms of this file elsewhere in this repo.

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

### Sqlite

This database was created with this python tool after the mysql dump file was turned into a full mysql db with a local user.

  - `pip install mysql-to-sqlite3`

```
mysql2sqlite --sqlite-file tree_parent.sqlite --mysql-database tree_parent --mysql-user <your_user> --prompt-mysql-password
```
 



