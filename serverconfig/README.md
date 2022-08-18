The files here were created and stored in git as a template.
Then they were added to .gitignore and the index updated as such:
`git update-index --assume-unchanged serverconfig/production/database.php`
so you can edit them with local or prod secrets as needed.


The purpose is to store environment specific configuration data here.
Then let an npm script from package.json copy the file into the server dir when deploying.

This is not the ideal solution as it requires everyone deploying to production
to have access to all of our secrets. A better solution is to store them on
the server as environment variables.

NearlyFreeSpeech's FAQ suggests creating a run script to set environment variables.
