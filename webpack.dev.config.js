var path = require('path');

var BUILD_DIR = path.resolve(__dirname, './tree-map/dist');
var CLIENT_DIR = path.resolve(__dirname, './tree-map/client');

var configs = require('./webpack.config');

var config = {
  entry: {
    client: path.join(CLIENT_DIR, './client.js'),
    vendor: configs.vendorList
  },
  output: {
    path: path.join(BUILD_DIR, "./js/"),
    filename: '[name]-bundle.js',
  },
  plugins: configs.corePluginList.concat(configs.devPluginList),
  devtool: 'eval',
  resolve: {
    // Absolute path that contains modules
    root: __dirname,
    // Directory names to be searched for modules
    modulesDirectories: ['libraries', 'node_modules'],
    extensions: ['', '.js', '.jsx'],
    alias: {
      'googletile' : path.join(__dirname, './node_modules/leaflet-plugins/layer/tile/Google.js'),
      'leaflet-canvas-marker' : path.join(__dirname, './libraries/leaflet-canvas-marker.js'),
      'iscroll' : path.join(__dirname, './libraries/iscroll-zoom.js'),
      // 'chartjs' : path.join(__dirname, './libraries/Chart.Core.js'),
      // 'createjs' : path.join(__dirname, './libraries/createjs.js')
    }
  },
  module : {
    loaders : configs.loaderList
  }
};

module.exports = config;
