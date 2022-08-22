These are a copy of the files running the server and client in production.

They are here to help find regressions.

Dont commit any changes to these files so they will always be a copy of production.

Feel free to alter them as needed to debug and compare changes without committing.

The npm scripts use these files to reset our test server back to a copy of production.

This is helpful in finding regressions until the rebuilt version of the app is ready to replace production. Then this directory and the npm scripts can be removed.

