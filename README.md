# FoodParent

_FoodParent_ is a web-based application as a part of Concrete Jungle’s _FoodParent_ project. 
The project helps to create virtual connection between trees and citizens who can provide status of fruits in Atlanta. 
The application provides a tool for creating a connection between trees
and people by posting status notes via smart devices and tracking history of trees over years to help Concrete Jungle 
decide the proper time for foraging fruits and distribute to the needy.

# Dir Structure

client: code for the browser
db: databases
dist: code for deployment, created by build scripts (not versioned, dont edit)
prod: contains server and browser code running in production. (dont edit)
env: files copied into other directories by npm scripts.
     they contain env specific variables to change how the app behaves locally vs production 

Notes:
Why have a prod dir instead of a prod branch?
- Production is running code that cannot be built reliably from any branch in the repo.
- Part of this initiative is to create a js bundle that can be reliably built from the repo.
- Until then a copy of the code as it is in production is stored in this dir for regression testing.
- The code can be uploaded to a dev server and run there to compare local changes with production.


# Development Setup

See [Dev_Setup](./docs/Dev_Setup.md)
