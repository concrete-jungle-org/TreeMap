#!/bin/sh
INDEX_FILE="./env/production/index.html" 
HOST=${CJ_PROD_USER}@${CJ_PROD_HOST}

## CLIENT BUILD
# create a production build of client and send to host
cp -f ${INDEX_FILE} .
cp -f env/production/server.json ./setting/
npm run build
rsync -az --stats ./dist/ ${HOST}:/home/public/tree-map/dist


## SERVER BUILD
rsync -az --stats --exclude server/.env --include ./server/ ${HOST}:/home/public/tree-map/server
# update lock file if any php vendor files changed
rsync -az --stats --include "./composer.*" --include ${INDEX_FILE} ${HOST}:/home/public/tree-map
# then run composer install
ssh ${HOST} /bin/bash <<EOF 
cd /home/public/tree-map
composer install;
EOF

# reset local files to the dev env version
cp -f env/local/index.html . 
cp -f env/local/server.json ./setting/


# IF the db needs to be updated, then run
# /home/protected/gh_dl_script.sh
# gets a new copy of db from the cj-airtable repo
# and then applies alter table statements to the result

# IF the db file location changed, verify the user, "web",
# has access to every dir from /public up to and including db file
