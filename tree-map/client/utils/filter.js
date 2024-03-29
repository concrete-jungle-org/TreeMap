import $ from 'jquery';
let ServerSetting = require('./../../setting/server.json');
import { FITERMODE } from './../utils/enum';

export function readFilter(resolve, reject) {
  // Create promise for importing the json file only one time over the program.
  let readFilterPromise = $.ajax({
    url: ServerSetting.uBase + ServerSetting.uServer + "filter.php",
    type: 'GET',
    data: {},
    cache: false,
    dataType: "json"
  });
  return readFilterPromise.then(function(response) {
    if (response.code == 200) {
      if (resolve)
        resolve(response);
    } else {
      if (__DEV__) {
        console.error('readFilterPromise error: ', response.message);
      }
      if (reject)
        reject(response.code);
    }
  }).catch(function(response) { // Error catch for calcSeason().
    if (__DEV__) {
      console.error('readFilterPromise error: ', response.statusText || response);
    }
    if (reject)
      reject(response.status);
  });
}

export function updateFilter(mode, ids, resolve, reject) {
  switch(mode) {
    case FITERMODE.FOOD:
      mode = 1;
      if (ids.length === 0) { // TODO: remove this if not needed
        ids.unshift('fake');  // Fake id to handle when there is no item in ids.
      };
      break;
    case FITERMODE.DEAD:
      mode = 2;
      ids.unshift(-1);  // Fake id to handle when there is no item in ids.
      break;
    case FITERMODE.OWNERSHIP:
      mode = 3;
      ids.unshift(-1);  // Fake id to handle when there is no item in ids.
      break;
    case FITERMODE.ADOPT:
      mode = 4;
      break;
    case FITERMODE.RATE:
      mode = 5;
      if (ids.length === 0) {
        ids.unshift(-1);  // Fake id to handle when there is no item in ids.
      };
      break;
    case FITERMODE.WEEKS:
      mode = 6;
      break;
  }
  let updateFilterPromise = $.ajax({
    url: ServerSetting.uBase + ServerSetting.uServer + "filter.php",
    type: 'POST',
    data: {
      'mode': mode,
      'ids': JSON.stringify(ids)
    },
    cache: false,
    dataType: "json"
  });
  return updateFilterPromise.then(function(response) {
    if (response.code == 200) {
      if (resolve)
        resolve(response);
    } else {
      if (__DEV__)
        console.error('updateFilterPromise error', error.message);
      if (reject)
        reject(response.code);
    }
  }).catch(function(response) { // Error catch for calcSeason().
    if (__DEV__)
      console.error('updateFilterPromise error', response);
    if (reject)
      reject(response.status);
  });
}

export function resetFilter() {
  // Create promise for importing the json file only one time over the program.
  let resetFilterPromise = $.ajax({
    url: ServerSetting.uBase + ServerSetting.uServer + "filter.php",
    type: 'PUT',
    data: {},
    cache: false,
    dataType: "json"
  });
  return resetFilterPromise.then(
    function(response) {
      return response;
    },
    function(error) {
      console.error('resetFilterPromise error', error.message);
    }
  );
}
