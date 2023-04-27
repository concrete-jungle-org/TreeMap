CREATE TABLE IF NOT EXISTS airtable_payloads (
  cursor INTEGER NOT NULL,
  webhook_id TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS airtable_job (
  payloads_id INTEGER NOT NULL,
  timestamp TEXT NOT NULL,
  description TEXT NOT NULL,
  status TEXT NOT NULL
);

--Note: contact is email address
CREATE TABLE IF NOT EXISTS "person" (
	"id" INTEGER NOT NULL,
	"auth" INTEGER NOT NULL,
	"name" VARCHAR(64) NOT NULL, 
	"contact" VARCHAR(128) NOT NULL,
	"password" CHARACTER(128) NOT NULL,
	"salt" CHARACTER(128) NOT NULL,
	"neighborhood" VARCHAR(128) NOT NULL,
	"active" TINYINT NOT NULL DEFAULT '1',
	"updated" DATETIME NOT NULL,
	PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX "contact" ON "person" ("contact");
