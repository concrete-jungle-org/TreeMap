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
      description: this.description,
      adopt: this.adopt == true ? "1" : "0",
      farm: this.farm == true ? "1" : "0"
    }
  }
  update(props) {
    this.id = parseInt(props.id);
    this.name = props.name;
    this.icon = props.icon;
    this.description = props.description;
    this.updated = moment(props.updated);
    this.adopt = props.adopt == "1" ? true : false;
    this.farm = props.farm == "1" ? true : false;
  }
}

class FoodStore {
  constructor() {
    this.foods = [];
    this.shadowImage = null;
    this.code = 200;
    // Bind action methods to store.
    this.bindListeners({
      handleFetchedFoods: FoodActions.FETCHED_FOODS,
      handleSetCode: FoodActions.SET_CODE,
      handleRegisterIcons: FoodActions.REGISTER_ICONS,
    });
    // Expose public methods.
    this.exportPublicMethods({
      getFood: this.getFood,
      getFoodIcons: this.getFoodIcons,
    });
  }

  getFood(id) {
    let foods = this.getState().foods.filter(food => food.id == id);
    if (foods.length == 1) {
      return foods[0];
    }
    return null;
  }
  getFoodIcons() {
    let result = [ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uShadowMarker];
    this.getState().foods.forEach((food) => {
      result.push(ServerSetting.uBase + ServerSetting.uStaticImage + food.icon);
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
    if (props[0].state == "fulfilled") {
      this.shadowImage = props[0].value;
    }
    for (let i = 0; i < this.foods.length; i++) {
      if (props[i + 1].state == "fulfilled") {
        this.foods[i].image = props[i + 1].value;
      }
    }
    this.code = 200;
  }
}

module.exports = alt.createStore(FoodStore, 'FoodStore');