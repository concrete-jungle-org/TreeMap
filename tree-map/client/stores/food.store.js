let alt = require('./../alt');
import moment from 'moment';

let FoodActions = require('./../actions/food.actions');
let FlagStore = require('./../stores/flag.store');

let ServerSetting = require('./../../setting/server.json');
let MapSetting = require('./../../setting/map.json');

export class FoodModel {
  constructor(props) {
    this.update(props);
  }
  toJSON() {
    return {
      id: this.id,
      name: this.name,
      icon: this.icon,
      adopt: this.adopt == true ? "1" : "0",
      farm: this.farm == true ? "1" : "0"
    }
  }
  update(props) {
    this.id = props.id;
    this.name = props.name;
    this.icon = props.icon;
    this.updated = moment(props.updated);
    if (!this.updated.isValid()) {
      this.updated = moment(new Date());
    }
    this.adopt = props.adopt == "1" ? true : false;
    this.farm = props.farm == "1" ? true : false;
  }
}

class FoodStore {
  constructor() {
    this.foods = [];
    this.shadowImage = null;
    this.checkImage = null;
    this.farmImage = null;
    this.tempImage = null;
    this.code = 200;
    // Bind action methods to store.
    this.bindListeners({
      handleFetchedFoods: FoodActions.FETCHED_FOODS,
      handleSetCode: FoodActions.SET_CODE,
      handleRegisterIcons: FoodActions.REGISTER_ICONS,
      handleUpdatedFood: FoodActions.UPDATED_FOOD,
    });
    // Expose public methods.
    this.exportPublicMethods({
      getFood: this.getFoodMemoized(),
      getFoodIcons: this.getFoodIcons,
    });
  }

  //Often executed against a filtered list of a single tree type all of which have same food
  //this operation is not expensive however. Memoizing it is a trivial performance gain
  getFoodMemoized(id) {
    let prevResult, prevId;
    return function(id) {
      if (id && id === prevId) {
        return prevResult;
      }
      let result;
      let foods = this.getState().foods.filter(food => {
        return food.id == id
      });
      if (foods.length == 1) {
        result = foods[0];
      }
      prevResult = result;
      prevId = id;
      return result;
    };
  }
  getFoodIcons() {
    let result = [ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uShadowMarker, ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uCheckMarkerIcon, ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uFarmMarkerIcon, ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uTemporaryMarkerIcon];
    this.getState().foods.forEach((food) => {
      if (food.icon) {
        result.push(ServerSetting.uBase + ServerSetting.uStaticImage + food.icon);
      } else {
        console.warn(`${food.name} has no associated icon to render on the map.`)
        result.push(ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uTemporaryMarkerIcon);
      }
    });
    return result;
  }
  handleFetchedFoods(props) {
    this.foods = [];
    props.forEach((prop) => {
      this.foods.push(new FoodModel(prop));
    });
    this.code = 200;
  }
  handleSetCode(code) {
    this.code = code;
  }
  handleRegisterIcons(props) {
    if (props[0].state == "fulfilled") {  // image[0]: marker shadow image
      this.shadowImage = props[0].value;
    }
    if (props[1].state == "fulfilled") {  // image[1]: marker check image
      this.checkImage = props[1].value;
    }
    if (props[2].state == "fulfilled") {  // image[2]: farm image
      this.farmImage = props[2].value;
    }
    if (props[3].state == "fulfilled") {  // image[3]: temp marker
      this.tempImage = props[3].value;
    }
    for (let i = 0; i < this.foods.length; i++) {
      this.foods[i].image = props[i + 4].value;
    }
    this.code = 200;
  }
  handleUpdatedFood(props) {
    let foods = this.foods.filter(food => food.id == props.id);
    if (foods.length == 1) {
      foods[0].update(props);
    }
    this.code = 200;
  }
}

module.exports = alt.createStore(FoodStore, 'FoodStore');
