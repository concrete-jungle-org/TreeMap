#!/bin/sh
INDEX_FILE="./env/production/index.html" 
HOST=${CJ_PROD_USER}@${CJ_PROD_HOST}

## CLIENT BUILD
# create a production build of client and send to host
cp -f ${INDEX_FILE} .
cp -f env/production/server.json ./setting/
npm run build
rsync -az --stats ./dist/ ${HOST}:/home/public/food-map/dist


## SERVER BUILD
rsync -az --stats --exclude server/.env --include ./server/ ${HOST}:/home/public/food-map/server
# update lock file if any php vendor files changed
rsync -az --stats --include "./composer.*" --include ${INDEX_FILE} ${HOST}:/home/public/food-map/
# then run composer install
ssh ${HOST} /bin/bash <<EOF 
cd /home/public/food-map/
composer install;
EOF

# reset local files to the dev env version
cp -f env/local/index.html . 
cp -f env/local/server.json ./setting/


# IF the db needs to be updated, then run
# /home/protected/gh_dl_script.sh
# gets a new copy of db from the cj-airtable repo
# and then applies 2 alter table statements to the result