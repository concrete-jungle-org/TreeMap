import $ from 'jquery';

let MapSetting = require('./../../setting/map.json');
import { sortLocationByDistanceASC } from './sort';

export function reverseGeocoding(coordinate, success, fail = console.error): void {
  var jqxhr = $.getJSON(MapSetting.uReverseGeoCoding + "&latlng=" + coordinate.lat + "," + coordinate.lng, function (data) {
    if (data.status == "OK") {
      if (success) {
        success({
          formatted: data.results[0].formatted_address
        });
      } else {
        fail("No success callback provided to function reverseGeocoding")
      }
    } else {
      fail(data.error_message);
    }
  }).fail(function (err) {
    //NOTE: these are network failures not API errors
    fail(err);
  });
}

export function geocoding(address, location, success, fail): void {
  var jqxhr = $.getJSON(MapSetting.uGeoCoding + "&address=" + address, function (data) {
    if (data.status == "OK") {
      let locations = data.results.sort(sortLocationByDistanceASC(location));
      if (locations.length > 0) {
        success(locations[0].geometry.location);
      } else {
        if (fail) {
          fail();
        }
      }
    }
  }).fail(function () {
    if (fail) {
      fail();
    }
  });
}
