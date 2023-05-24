#! /bin/sh
set -u

HOST=www.concrete-jungle.org
BASE_ID=appqXz1ZlM1RT2nFd
WEBHOOK_ID=achPWgtJ24DHB5KxP


# A function to create readable post data with interpolation
post_data()
{
  cat <<EOF
{
  "base": {
    "id": $BASE_ID
  },
  "webhook": {
    "id": $WEBHOOK_ID
  },
  "timestamp": "2000-01-01T01:00:00.000Z"
}
EOF
}


curl -v POST https://$HOST/tree-map/server/webhooks.php \
-H "Content-Type: application/json" \
--data "$(post_data)"

