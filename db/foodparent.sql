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
