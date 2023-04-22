#!/bin/zsh
set -u

BASE_ID=${AIRTABLE_DEV_BASE_ID}
WEBHOOK_ID=${AIRTABLE_STAGING_WEBHOOK_ID}
TOKEN=${AIRTABLE_STAGING_TOKEN}

curl -X POST "https://api.airtable.com/v0/bases/$BASE_ID/webhooks/$WEBHOOK_ID/refresh" \
-H "Authorization: Bearer $TOKEN"

