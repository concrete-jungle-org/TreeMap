#!/bin/zsh
set -u

BASE_ID=${AIRTABLE_DEV_BASE_ID}
TOKEN=${AIRTABLE_STAGING_TOKEN}

curl "https://api.airtable.com/v0/bases/$BASE_ID/webhooks" \
-H "Authorization: Bearer $TOKEN"

