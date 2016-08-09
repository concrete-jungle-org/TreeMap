let alt = require('../alt');

import ImagePreloader from 'image-preloader-promise';

import { updateSeason } from './../utils/season';
import { getLocalization, setLocalization } from './../utils/localization';
let FoodSource = require('./../sources/food.source');
let FoodStore = require('./../stores/food.store');
let FoodActions = require('./../actions/food.actions');
let FlagSource = require('./../sources/flag.source');
let FlagActions = require('./../actions/flag.actions');
let TreeSource = require('./../sources/tree.source');
let TreeActions = require('./../actions/tree.actions');

import { MESSAGETYPE } from './../utils/enum';

class InitActions {
  setCode(code) {
    return (dispatch) => {
      dispatch(code);
    }
  }

  // Fetch all necessary data in the beginning using chain callbacks.
  initialize(id = 0) {
    let self = this;
    self.setMessage(MESSAGETYPE.SUCCESS, "Initializing Application...");
    return (dispatch) => {
      dispatch();
      this.setCode(90);
      // Fetch foods.
      self.setMessage(MESSAGETYPE.SUCCESS, "Importing Food Data...");
      FoodSource.fetchFoods()
      .then((response) => {
        FoodActions.fetchedFoods(response);
      })
      .then(() => {
        // Fetch flags.
        self.setMessage(MESSAGETYPE.SUCCESS, "Importing Flag Data...");
        FlagSource.fetchFlags().then((response) => {
          FlagActions.fetchedFlags(response);
        })
        .then(() => {
          self.setMessage(MESSAGETYPE.SUCCESS, "Updating Season Data...");
          updateSeason()
          .then(function(response) {
            // Fetch trees.
            self.setMessage(MESSAGETYPE.SUCCESS, "Importing Tree Data...");
            TreeSource.fetchTrees(id)
            .then((response) => {
              TreeActions.fetchedTrees(response);
            })
            .then((response) => {
              self.setMessage(MESSAGETYPE.SUCCESS, "Importing Image Assets...");
              let icons = FoodStore.getFoodIcons();
              ImagePreloader.preloadImages(icons)
              .then(function (data) {
                if (icons.length != data.length) {
                  if (__DEV__) {
                    console.error("One or more of food icons are missing. Please check the image files.");
                  }
                }
                FoodActions.registerIcons(data);
              })
              .then(() => {
                self.setMessage(MESSAGETYPE.SUCCESS, "Importing Localization Data...");
                getLocalization(window.navigator.userLanguage || window.navigator.language)
                .then(function(response) {
                  setLocalization(response);
                })
                .then(() => {
                  // Start the app.
                  self.setMessage(MESSAGETYPE.SUCCESS, "Rendering Markers...");
                  self.loaded();
                  setTimeout(function() {
                    self.setMessage(MESSAGETYPE.SUCCESS, "Let's Do Parenting!");
                  }, 1000);
                  setTimeout(function() {
                    self.hideSplashPage();
                  }, 2500);
                })
                .catch(function (response) { // Error catch for getLocalization().
                  if (response.status == 200) {
                    self.setMessage(MESSAGETYPE.FAIL, `Failed to import localization data.`);
                    if (__DEV__) {
                      console.error(`Failed to import localization data. This could happen either because the file doesn't exist, or the internet is disconnected.`);
                    }
                  } else {
                    self.setMessage(MESSAGETYPE.FAIL, response.status);
                    if (__DEV__) {
                      console.error(`Failed to import localization data. Error code: ${response.status}`);
                    }
                  }
                });
              })
              .catch(function (code) { // Error catch for preloadImages().
                self.setMessage(MESSAGETYPE.FAIL, code);
                if (__DEV__) {
                  console.error(code);
                  console.error('None of the images were able to be loaded! Please check the internet connection.');
                }
              });
            })
            .catch((code) => {  // Error catch for fetchTrees().
              self.setMessage(MESSAGETYPE.FAIL, code);
              if (__DEV__) {
                console.error(code);
              }
              // this.setCode(code);
            });
          })
          .catch(function(response) { // Error catch for calcSeason().
            if (response.status == 200) {
              self.setMessage(MESSAGETYPE.FAIL, `Failed to update season data.`);
              if (__DEV__) {
                 console.error(`Failed to update season data. This could happen either because the file doesn't exist, or the internet is disconnected.`);
              }
            } else {
              self.setMessage(MESSAGETYPE.FAIL, `Failed to update season data. Error code: ${response.status}`);
              if (__DEV__) {
                console.error(`Failed to update season data. Error code: ${response.status}`);
              }
            }
          });
        })
        .catch((code) => {  // Error catch for fetchFlags().
          self.setMessage(MESSAGETYPE.FAIL, code);
          if (__DEV__) {
            console.error(code);
          }
          // this.setCode(code);
        });
      })
      .catch((code) => {  // Error catch for fetchFoods().
        self.setMessage(MESSAGETYPE.FAIL, code);
        if (__DEV__) {
          console.error(code);
        }
        // this.setCode(code);
      });
    }
  }

  setCode(code) {
    return (dispatch) => {
      dispatch(code);
    }
  }
  setMessage(type, value) {
    return (dispatch) => {
      dispatch({type: type, value: value});
    }
  }
  loaded() {
    return (dispatch) => {
      dispatch();
    }
  }
  hideSplashPage() {
    return (dispatch) => {
      dispatch();
    }
  }
}

module.exports = alt.createActions(InitActions);