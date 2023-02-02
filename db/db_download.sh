#! /bin/sh

# REQUIREMENTS
# ---
# GITHUB_OAUTH_TOKEN='<a token which grants access to natomato/cj-airtable>'
# File exists /home/public/food-map/db

curl -v \
  -H 'Authorization: Bearer '${GITHUB_OAUTH_TOKEN} \
  -H 'Accept: application/vnd.github.v3.raw' \
  -o '/home/public/food-map/db/airtable.sqlite'\
  -L 'https://api.github.com/repos/natomato/cj-airtable/contents/tree_parent.db'

exec sqlite3 /home/public/food-map/db/airtable.sqlite < /home/public/food-map/db/alter_food_col.sql
