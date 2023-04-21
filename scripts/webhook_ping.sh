#! /bin/sh

# Note: this is only an example of the script stored at our nfshost's /home/protected/ directory
# I do not know how to set env variables on the host, so for the real script I have replaced the
# variables with their values, but left them out here so the file is safe to version.

# REQUIREMENTS
# BASE_ID = app67TJUmomYwOYsb (staging)
# WEBHOOK_ID = xxx (staging)
# AIRTABLE_PAT = xxx (staging)
curl -X POST "https://api.airtable.com/v0/bases/${BASE_ID}/webhooks/${WEBHOOK_ID}/refresh" \
-H "Authorization: Bearer ${AIRTABLE_PAT}"
