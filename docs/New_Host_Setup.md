Do i need seed data too?
or more simply just copy the existing db to the new location

Add to documentation that this is how the db is created

Setup a new Environment
---

For example, if you wanted to create a 2nd staging environment or move the
production env to a new host.

get new staging up and running locally
  mv db files into new repo
  node v6
  npm install 
  npm run dev
  manually create .env file from the .env.example, same dir ./server
  mkdir db
  rsync the sqlite3 files
  - configured .ssh/config with new hostname to use the new users key file. Note
  this will prevent me from accessing my own nfs site. i need to find a better
  way
  - rsync -azP airtable.sqlite durkin_cj-staging@ssh.phx.nearlyfreespeech.net:/home/public/tree-map/db
  correct file permissions and ownership
  - chmod 664 airtable.sqlite
  - chmod 664 treemap.sqlite
  - chgrp web airtable.sqlite
  - chgrp web treemap.sqlite
  - chmod 775 ../db
  - chgrp web ../db
  - chmod 775 ../tree-map
  - chgrp web ../tree-map


