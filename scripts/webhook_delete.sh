#! /bin/sh
set -eu

WEBHOOK_ID=achNUIK8fH9EFguhf

curl -X DELETE https://api.airtable.com/v0/bases/${AIRTABLE_BASE_ID}/webhooks/${WEBHOOK_ID} \
-H "Authorization: Bearer ${AIRTABLE_PROD_TOKEN}"

