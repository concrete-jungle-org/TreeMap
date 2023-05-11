## Initial dev setup

If this is too long, a faster setup is to skip the php/apache sections and instead edit your files locally 
but then `build`/`deploy` them to remote host and view changes there by visiting the [public url](https://cj-staging.nfshost.com/).

[Take a look at the app](https://cj-staging.nfshost.com/) running on a dev server to see what we are buildling.

- Get ssh access to the development server: `cj-staging`
  - `ssh -i <ssh_id_file> <your_user>_cj-staging@ssh.phx.nearlyfreespeech.net`
- install node v6
  - However, if you need to run 'npm install' then be careful, node v6 ships with npm v3 which does not read the package-lock file
  - The lock file is needed to ensure the exact same dependencies are installed
  - If you see errors when building that node-sass does not yet support your runtime environment, you are probably not running node v6
  - the version of node-sass used here only supports up to node v7
- npm install
  - NOTE: a package-lock was created using a newer version of node (v16) because node v6 use npm v3
  - npm v3 knows nothing about package-lock file
  - npm v3 might still install the correct dependency tree since the package.json specifies exact versions of all dependencies
  - if you are having one way to be more sure the dep tree you installed is the same as other devs is to rm -rf node_modules
  - then switch to a newer node v with npm > 5, then npm install - this will follow the package-lock.json maintained in git.
  - if you ever add/remove/update a dependency, you should do so with npm > 5 so that the package-lock file is updated as well.
- create ENV variables to enable access to the ssh host
  - Here are mine for example:
  - `export CJ_PROD_USER='durkie_cj-staging'`
  - `export CJ_PROD_HOST='ssh.phx.nearlyfreespeech.net'`
- Set up your local database files
  - See the [readme.md](../db/readme.md) about how to download the sqlite database 
  - `chmod 664 tree_parent.sqlite` verify that you allow user and group rw permissions if getting an access error
- Setup PHP on macOS
  - brew install php@8
  - php -v
  
  - Notes from the original readme concerning PHP, which was v7.4 at that time.
    * Open `php.ini` configuration file under {apach} directory. If you are using external hosting service, ask hosting manager.
    * Find `upload_max_filesize` and set the value higher than 6M. This value defines the maximum size of file, and some of image files generated from smart devices exceeds 4M.
    ```
    ; Maximum allowed size for uploaded files.
    ; http://php.net/upload-max-filesize
    upload_max_filesize=6M
    ```



- Setup Apache on MacOS 12.0 Monterrey (Intel Based)
  - Note: Apple silicon (M1+) computers may store files in a different location.
  - [Setting up Apache Server on macOS 12](https://getgrav.org/blog/macos-monterey-apache-multiple-php-versions)
  - Also following instructions to load apache from: https://www.git-tower.com/blog/apache-on-macos/
      ```sh
      ❯ which httpd
      /usr/sbin/httpd
      ```
      - TODO: research why my local doesn't show this and httpd services not running
      - but the app still loads as expected
  - Make changes to the conf file already used by MacOS.
  - `/usr/local/etc/httpd/httpd.conf`
    - Change the port from 8080 to 80
    - Enable PHP@8, if not default
    - Enable vhosts
    - Change the user/group to your username/staff
  - In the `extras/vhosts` file remove dummy vhosts and create a new one
  - Remember to start this service
    - `brew services start httpd`
- `npm run dev`
- visit localhost:3000
- in network tab verify by checking the line with name "localhost" and in the response tab you can should see the contents of the index.html file 

## Various Ways to Build the App

### Main dev workflow 

The main dev branch uses node v6, webpack v1, and the airtable.sqlite database.

#### In order to run on your local machine

- Edit the env/local && serverconfig/local files to match your local file paths as needed
- not all files here need to be changed, you can run git diff to see what the script `setup:dev` alters
- `npm run setup:dev` to use files and settings specific to your local env
  - This is a temp solution until a proper .env file is set up, unfortunately this temp solution requires you to reset the env when committing
  - `npm run setup:prod` when you are ready to rebase or commit changes
  - `npm run build` to bundle all the js into the /dist folder where its read by apache
- `npm run dev`: webpack bundles and provides a link to localhost:3000
- verify the webapp loads ok: open the link, 
  - inspect the console messages 
  - inspect the Network tab, verify you get 200 responses with json payloads

#### In order to run on the server

For the js web app:
- `npm run build`
- `npm run deploy`

For the python server:
- `npm run setup:prod` 
- `npm run sync:server`

Visit [cj-staging.nfshost.com to see changes](https://cj-staging.nfshost.com/)


## Misc dev notes

Note: There is a local an production version of index.html because prod loads everything from a sub-dir `/tree-map` so the script src starts with `/tree-map/dist`.
Note: Google API key is hardcoded in a couple files
- index.html: `<script src="https://maps.googleapis.com/maps/api/js?key=`
- settings/map.json: `"uReverseGeoCoding": "https://maps.googleapis.com/maps/api/geocode/json?key=`
- settings/map.json: `"uGeoCoding": "https://maps.googleapis.com/maps/api/geocode/json?key=`


To prevent alerts a member of the team with google admin access to the key needs to add restrictions to how the key can be used
The key is used by the users browser. It is bundled in the client-bundle.js. Deploying a new key will require changing
these values in the source code and then creating a new dist/js/client-bundle.

Note: MapBox API key is also exposed
- map.json: `"sMapboxAccessToken": "..."`

The mysql db is already loaded on the remote host.
This can be confirmed from the [phpmyadmin dashboard](https://phpmyadmin.nearlyfreespeech.net/)

### If you want to run the old dev workflow locally you will need to set up mysql/php/apache

  - `brew install mysql@5.7`
  - `brew link --force mysql@5.7`
 
Note: If you make a mistake you can remove and reinstall
- `brew uninstall mysql@5.7`
- `rm -rf /usr/local/var/mysql`
- `rm /usr/local/etc/my.cnf`
- you may need to edit your PATH var if a 2nd version of mysql installed that is hidden by PATH export
- source
- check: `mysql --version`
- `brew uninstall mysql`
Some unusual notes about production:
- Production code is on the `scss` branch with a number of file changes that are not tracked
- Github's `master` branch has 1 commit ahead of the `scss` branch where the intro-js code was removed


### From the original Readme.md
  - Upload files in a server
  **Don't** try to upload all files in {app-root-directory}. It have a lot of dependency libraries which don't need to run the application.
  Below are the list of directories and files require to run the applicaiton.
  * content/
  * dist/
  * favicons/
  * server/
  * static/
  * index.html
  * .htaccess
  
## Notes for manual setup

To create a webhook that listens to changes on the `tree` table see the
[webhook_create.sh](../scripts/webhook_create.sh)

list active webhooks
```sh
curl "https://api.airtable.com/v0/bases/${AIRTABLE_BASE_ID}/webhooks" \
-H "Authorization: Bearer ${AIRTABLE_PERSONAL_ACCESS_TOKEN}"
```

```
sqlite3 treemap.sqlite
chgrp web ./treemap.sqlite
cmod 664 ./treemap.sqlite

//create tables if not exist
```

