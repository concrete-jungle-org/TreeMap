# Dev Setup

## Local Setup

Steps taken on macOS 12.4 intel
- install node v6
- npm install

Get ssh access to the development server: `nate`
- `ssh <your_user>_nate@ssh.phx.nearlyfreespeech.net`

Get a copy of tree_parent.sqlite
- download from development server, its located in the `FoodParent2.0/db/`
- `chmod 664 tree_parent.sqlite` allow user and group rw permissions


Initial setup
- npm install
  - NOTE: a package-lock was created using a newer version of node (v16) because node v6 use npm v3
  - npm v3 knows nothing about package-lock file
  - npm v3 might still install the correct dependency tree since the package.json specifies exact versions of all dependencies
  - if you are having one way to be more sure the dep tree you installed is the same as other devs is to rm -rf node_modules
  - then switch to a newer node v with npm > 5, then npm install - this will follow the package-lock.json maintained in git.
  - if you ever add/remove/update a dependency, you should do so with npm > 5 so that the package-lock file is updated as well.
- create ENV variables to enable access to the ssh host
  - Here are mine for example:
  - `export CJ_PROD_USER='nate91711_nate'`
  - `export CJ_PROD_HOST='ssh.phx.nearlyfreespeech.net'`

Dev workflow
- Edit the env/local && serverconfig/local files to match your local file paths as needed
- not all files here need to be changed, you can run git diff to see what the script `setenv:local` alters
- `npm run setenv:local` to use files and settings specific to your local env
- This is a temp solution until a proper .env file is set up, unfortunately this requires you to reset the env when committing
- `npm run setenv:prod` when you are ready to rebase or commit changes
- `npm run build` to bundle all the js into the /dist folder where its read by apache
- `npm run deploy`: to copy the client (js) files to the webhost
- `npm run sync:server`: to copy the server files to the webhost


Why node v6?
- If you see errors when building that node-sass does not yet support your runtime environment, the version of node-sass used here
  - only supports up to node v7
  - However, if you need to run 'npm install' then be careful, node v6 ships with npm v3 which does not read the package-lock file
  - The lock file is needed to ensure the exact same dependencies are installed

Make a change to the js, then:
- `npm run build`
- `npm run sync:client`
- visit [nate.nfshost.com to see changes](https://nate.nfshost.com/food-map/)

Note: Google API key is hardcoded in a couple files
- index.html: `<script src="https://maps.googleapis.com/maps/api/js?key=`
- settings/map.json: `"uReverseGeoCoding": "https://maps.googleapis.com/maps/api/geocode/json?key=`
- settings/map.json: `"uGeoCoding": "https://maps.googleapis.com/maps/api/geocode/json?key=`


To prevent alerts a member of the team with google admin access to the key needs to add restrictions to how the key can be used
The key is used by the users browser. It is bundled in the client-bundle.js. Deploying a new key will require changing
these values in the source code and then creating a new dist/js/client-bundle.

Note: MapBox API key is also exposed
- map.json: `"sMapboxAccessToken": "..."`

# Setup Notes

- node v6, webpack v1
- [Node v6 to v8 Breaking Changes](https://github.com/nodejs/wiki-archive/blob/master/Breaking-changes-between-v6-LTS-and-v8-LTS.md)
- [Webpack v1 documentation](https://github.com/webpack/docs/wiki/contents)

Steps taken on macOS 12.4 intel
- install node v6
- npm install
- npm install -g webpack-dev-server@1
- webpack-dev-server --port 3000
- npm run dev
- visit localhost:3000
- in network tab verify by checking the line with name "localhost" and in the response tab you can should see the contents of the index.html file 


Note: index.html loads scripts with an href that starts with `/food-map/dist`. 
Production code is on the `scss` branch with a number of file changes that are not tracked
Github's `master` branch has 1 commit ahead of the `scss` branch where the intro-js code was removed

