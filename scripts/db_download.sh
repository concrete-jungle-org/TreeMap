#! /bin/sh
set -eu

GITHUB_PAT=${A_TOKEN_YOU_CREATE_FOR_THIS_PURPOSE}

curl -v \
  -H "Authorization: Bearer $GITHUB_PAT" \
  -H 'Accept: application/vnd.github.v3.raw' \
  -o '/home/public/tree-map/db/airtable.sqlite'\
  -L 'https://api.github.com/repos/concrete-jungle-org/cj-airtable/contents/tree_parent.db'

exec chgrp web /home/public/tree-map/db/airtable.sqlite
exec chmod 664 /home/public/tree-map/db/airtable.sqlite
exec sqlite3 /home/public/tree-map/db/airtable.sqlite < /home/public/tree-map/db/alter_food_col.sql
exec sqlite3 /home/public/tree-map/db/airtable.sqlite < /home/public/tree-map/db/create_in_season_tbl.sql
