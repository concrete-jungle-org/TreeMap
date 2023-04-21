
## Directory Contents:

- `deploy.sh`: used by package.json to build & deploy local files to the staging server
- `deployed_files.txt`: used by .github/workflows/staging.yml to copy files from staging branch to server
- `db_download.sh`: used by nfshost cron job to fetch database from cj-airtable where a new backup is made nightly
- `webhook_ping.sh`: used by nfshost cron job to keep alive the webhooks (expire every 7 days)

### Webhook Ping

The script requires a personal access token with the correct scope to refresh a particular webhook.
See the airtable docs about webhook refresh, also see docs/Dev_Setup.md for details on how to get the webhook id.

