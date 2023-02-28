CREATE TABLE IF NOT EXISTS airtable_jobs (
  id INTEGER NOT NULL PRIMARY KEY,
  cursor INTEGER NOT NULL,
  timestamp TEXT NOT NULL,
  jobs TEXT NOT NULL
);

INSERT INTO airtable_jobs 
       (id, cursor, timestamp, jobs) 
values (440, 8, "2023-02-22T15:36:04.021Z", ""); 

--TODO: add webhook_id, rename id to baseTransactionNumber
CREATE TABLE IF NOT EXISTS airtable_cursor (
  cursor INTEGER NOT NULL
);

INSERT INTO airtable_cursor (cursor) VALUES (8);

