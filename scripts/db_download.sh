#! /bin/sh

# REQUIREMENTS
# ---
GITHUB_PAT='<a personal access token user creates to access concrete-jungle-org/cj-airtable>'
# File exists /home/public/tree-map/db

curl -v \
  -H 'Authorization: Bearer ' $GITHUB_PAT \
  -H 'Accept: application/vnd.github.v3.raw' \
  -o '/home/public/tree-map/db/airtable.sqlite'\
  -L 'https://api.github.com/repos/concrete-jungle-org/cj-airtable/contents/tree_parent.db'

exec sqlite3 /home/public/tree-map/db/airtable.sqlite < /home/public/tree-map/db/alter_food_col.sql
exec sqlite3 /home/public/tree-map/db/airtable.sqlite < /home/public/tree-map/db/create_in_season_tbl.sql
