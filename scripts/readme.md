
## Directory Contents:

- `deploy.sh`: used by package.json to build & deploy local files to the staging server
- `deployed_files.txt`: used by .github/workflows/staging.yml to copy files from staging branch to server
- `db_copy.sh`: used by nfshost cron job to prepare new db for use. This script replaces the old db_download script because we no longer store the db in github.
- `db_download.sh`: used by nfshost cron job to fetch database from cj-airtable where a new backup is made nightly
- `webhook_refresh.sh`: used by nfshost cron job to keep alive the webhooks (expire every 7 days)
- `webhook_create.sh`: template to use as needed to create a new webhook

### Webhook Ping

The script requires a personal access token with the correct scope to refresh a particular webhook.
See the [airtable docs](https://airtable.com/developers/web/api/refresh-a-webhook) about webhook refresh.
Also see the [setup docs](../docs/Dev_Setup.md) for details on how to get the webhook id.

## Github Workflows

Note the deploy.sh script is not used regularly and may not get maintained.
Deployments happen automatically when code is pushed to staging or main branches.
The [.github/workflows/staging.yml](../.github/workflows/staging.yml) details how.
