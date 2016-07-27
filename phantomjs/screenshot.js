"use strict";
var page = require('webpage').create(),
  system = require('system'),
  Settings = require('./../src/constraints/settings.json'),
  address, output, size, pageWidth, pageHeight;

page.viewportSize = { width: 400, height: 300 };

page.open("http://localhost:3000" + Settings.uBaseName + Settings.uServer + "screenshot.php", 'get', {}, function (status) {
  if (status !== 'success') {
    console.log('Unable to post!');
  } else {

    var content = page.content.replace("<html><head></head><body>", "");
    var content = content.replace("</body></html>", "");
    var json = JSON.parse(content);
    if (json.code != 200) {
      console.log(json.message);
      phantom.exit();
    }

    window.setTimeout(function () {
      phantom.exit();
    }, 10000 * json.trees.length);
    for (var i = 0; i < json.trees.length; i++) {
      var index = i + 1;
      var id = json.trees[i].id;
      var address = "http://localhost/FoodParent2.0/tree/" + json.trees[i].id;
      window.setTimeout(function (id, address, index, total) {
        page.open(address, function (status) {
          if (status !== 'success') {
            console.log('Unable to load the address!');
            phantom.exit(1);
          } else {
            window.setTimeout(function () {
              console.log("Rendering " + id + "_map.png (" + index + " out of " + total + ")");
              page.render("../dist/map/" + id + "_map.png");
            }, 7500);
          }
        });
      }, 10000 * i, id, address, index, json.trees.length);
    }
  }
});