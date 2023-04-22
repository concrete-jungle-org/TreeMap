#!/bin/zsh
set -u

HOST=${TREE_MAP_HOST}
BASE_ID=${AIRTABLE_DEV_BASE_ID}
TREE_TBL=${AIRTABLE_DEV_TREE_TBL}
TOKEN=${AIRTABLE_STAGING_TOKEN}
# NOTE: personal access tokens are created and managed
# via the airtable webui, to work a PAT requires:
# scopes: webhook:manage, data.records:read, 
# access: the staging or production database

# A function to create readable post data with interpolation
post_data()
{
  cat <<EOF
{
  "notificationUrl": "https://$HOST/tree-map/server/webhooks.php",
  "specification": {
    "options": {
      "filters": {
        "fromSources": ["client"],
        "dataTypes": ["tableData"],
        "recordChangeScope": "$TREE_TBL"
      }
    }
  }
}
EOF
}

curl -X POST "https://api.airtable.com/v0/bases/${BASE_ID}/webhooks" \
-H "Authorization: Bearer ${TOKEN}" \
-H "Content-Type: application/json" \
--data "$(post_data)"

