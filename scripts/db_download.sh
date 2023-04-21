#! /bin/sh

# REQUIREMENTS
# ---
# GITHUB_OAUTH_TOKEN='<a token which grants access to concrete-jungle-org/cj-airtable>'
# File exists /home/public/tree-map/db

curl -v \
  -H 'Authorization: Bearer '${GITHUB_OAUTH_TOKEN} \
  -H 'Accept: application/vnd.github.v3.raw' \
  -o '/home/public/tree-map/db/airtable.sqlite'\
  -L 'https://api.github.com/repos/concrete-jungle-org/cj-airtable/contents/tree_parent.db'

exec sqlite3 /home/public/tree-map/db/airtable.sqlite < /home/public/tree-map/db/alter_food_col.sql
