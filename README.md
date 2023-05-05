# TreeMap

_TreeMap_ is a map of fruit trees maintained by Concrete Jungle. 
The project helps to create virtual connection between trees and citizens who can provide status of fruits in Atlanta. 
The application provides a tool for creating a connection between trees
and people by posting status notes via smart devices and tracking history of trees over years to help Concrete Jungle 
decide the proper time for foraging fruits and distribute to the needy.

# Dir Structure

- docs: more detailed documentation
- libraries: 3rd party js code bundled by webpack into a single js asset
- tree-map: all the code hosted on remote server that runs the app
  - client: code for the browser
  - db: databases, one for airtable and one for tree-map specific data
  - dist: code for deployment, created by build scripts, not versioned.
  - server: php files used by web-server

# Development Setup

See [Dev_Setup](./docs/Dev_Setup.md)
