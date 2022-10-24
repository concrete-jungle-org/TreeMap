# Setup for replacing the database

Production
- uses original code and mysql database
- inspect the network tab to see data payloads are expected from sever

NFS Host Server
- setup to run the copy of prod code with the copy of prod mysql database
- the host can be reset to this state with: `npm run reset:database` && `npm run reset:server`
- this creates a known good reference point to compare your local changes against

Local Server
- same as NFS Host
- dbeaver-community is good for testing
- I think npm setup:dev will point the local repo to the tree_parent.sqlite db, which means familiar code as prod with sqlite instead of mysql
- the goal now is to get the web app to function same as it was with mysql.db, so fix the queries that use mysql specific features
- also i want to make sure the same amount of data is returned when the queries work
- note: comparing local to production is not correct, because prod data might be changing as data in that database could be added/updated
- compare local .sqlite to NFS server's .mysql
