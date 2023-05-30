#! /bin/sh
set -eu

WEBHOOK_ID="achsYhE6xuJMN3jpl"

curl -X DELETE https://api.airtable.com/v0/bases/${AIRTABLE_DEV_BASE_ID}/webhooks/${WEBHOOK_ID} \
-H "Authorization: Bearer ${AIRTABLE_STAGING_TOKEN}"

