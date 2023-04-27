import React from 'react';
import ReactTooltip from 'react-tooltip';
import AltContainer from 'alt-container';
import Select from 'react-select';
import TreeSeason from './tree-season.component';


require('./tree-food.component.scss');
var FontAwesome = require('react-fontawesome');
let ServerSetting = require('./../../setting/server.json');
let MapSetting = require('./../../setting/map.json');

import { localization } from './../utils/localization';
import { FITERMODE } from './../utils/enum';
import { updateFilter, resetFilter } from './../utils/filter';

let FoodStore = require('./../stores/food.store');
let FlagStore = require('./../stores/flag.store');

let TreeStore = require('./../stores/tree.store');
let TreeActions = require('./../actions/tree.actions');

function isValidRange(start, finish) {
  if (!start || !finish) return false;
  if (start > finish) return false;
  if (start < 1 || start > 52) return false;
  if (finish < 2 || finish > 53) return false;
  return true;
}
function range(start, finish) {
  if (!isValidRange(start, finish)) {
    throw new Error(`Unable to filter tree food by invalid range: ${start} to ${finish}`);
  }
  return [...Array(finish - start + 1).keys()].map(i => i + start);
}

export default class TreeFood extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.updateAttribute = this.updateAttribute.bind(this);
    this.renderOptionValue = this.renderOptionValue.bind(this);
    this.updateFirstWeek = this.updateFirstWeek.bind(this);
    this.updateLastWeek = this.updateLastWeek.bind(this);
    this.filterBySeason = this.filterBySeason.bind(this);
    this.resetFoodsFilter = this.resetFoodsFilter.bind(this);

    this.state = {
      options: null, 
      selected: null,
      firstWeek: props.weeks[0] || 1,
      lastWeek: props.weeks[props.weeks.length - 1] || 2,
      isValid: true
    };
  }
  componentDidMount () {
    this.updateProps(this.props);
  }
  componentWillReceiveProps(nextProps) {
    this.updateProps(nextProps);
  }
  updateProps(props) {
    let state = {};
    let options = [];
    let selected = null;
    let foods = FoodStore.getState().foods;
    let propsFoods = props.foods || [];
    foods.forEach(food => {
      if (!food.farm) {
        options.push({value: food.id, label: food.name});
        if (propsFoods.indexOf(food.id) > -1) {
          if (selected == null) {
            selected = [];
          }
          selected.push({value: food.id, label: food.name});
        }
      }
    });
    state.options = options;
    state.selected = selected;
    const weeks = props.weeks;
    if (weeks && weeks.length > 0) {
      state.firstWeek = weeks[0]; 
      state.lastWeek = weeks[weeks.length-1]; 
    }
    this.setState(state);
  }
  renderOptionValue(option) {
    let icon;
    let food = FoodStore.getFood(option.value);
    if (food && food.icon) {
      icon = ServerSetting.uBase + ServerSetting.uStaticImage + food.icon;
    } else {
      if (parseInt(option.value) == -1) {
        icon = ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uFarmMarkerIcon;
      } else {
        icon = ServerSetting.uBase + ServerSetting.uStaticImage + MapSetting.uTemporaryMarkerIcon;
      }
    }
    return <span className="tree-food"><img className="tree-food-icon" src={icon} /><span className="tree-food-name">{option.label}</span></span>;
  }
  updateAttribute(selected) {
    var foods = [];
    if (selected) {
      selected.forEach(option => {
        foods.push(option.value);
      });
    }
    updateFilter(FITERMODE.FOOD, foods, function(response) {  // Resolve
      TreeActions.fetchTrees();
    }, function(response) { // Reject

    });
    this.setState({selected: selected});
  }
  updateFirstWeek(value) {
    const firstWeek = parseInt(value, 10);
    const isValid = isValidRange(firstWeek, this.state.lastWeek);
    this.setState({firstWeek, isValid});
  }
  updateLastWeek(value) {
    const lastWeek = parseInt(value, 10);
    const isValid = isValidRange(this.state.firstWeek, lastWeek);
    this.setState({lastWeek, isValid})
  }
  filterBySeason() {
    const weeks = range(this.state.firstWeek, this.state.lastWeek);
    var self = this;
    updateFilter(FITERMODE.WEEKS, weeks, function(response) {
      if (response.code == 200) {
        TreeActions.fetchTrees();
        let props = {
          foods: response.foods,
        };
        self.updateProps(props);
      } else {
        if (__DEV__)
          console.error(response.message);
        if (reject)
          reject(response.code);
      }
    });
  }
  resetFoodsFilter() {
    this.setState({
      firstWeek: this.props.weeks[0],
      lastWeek: this.props.weeks[this.props.weeks.length - 1]
    });
    resetFilter().then(function(response) {
      if (response.code == 200) {
        TreeActions.fetchTrees();
        let props = {
          foods: response.foods,
        };
        this.updateProps(props);
      } else {
        if (__DEV__)
          console.error(response.message);
        if (reject)
          reject(response.code);
      }
    }.bind(this)).catch(function(response) { // Error catch for calcSeason().
      if (__DEV__)
        console.error(response.statusText || response);
      if (reject)
        reject(response.status);
    });
  }
  render () {
    return (
      <div className="tree-filter-wrapper">
        <div className="filter-label">
          <FontAwesome className='' name='apple' />{localization(633)}
        </div>
        <div className="filter-data brown-medium-multi">
          <Select name="food-select" 
            multi={true} 
            clearable={true} 
            searchable={true} 
            scrollMenuIntoView={false} 
            options={this.state.options} 
            value={this.state.selected} 
            valueRenderer={this.renderOptionValue} 
            optionRenderer={this.renderOptionValue} 
            onChange={this.updateAttribute} 
            placeholder={localization(642)} 
            backspaceToRemoveMessage="" />
        </div>
        <TreeSeason 
          isValid={this.state.isValid}
          firstWeek={this.state.firstWeek}
          lastWeek={this.state.lastWeek}
          updateFirstWeek={this.updateFirstWeek}
          updateLastWeek={this.updateLastWeek}
          resetFoodsFilter={this.resetFoodsFilter}
          filterBySeason={this.filterBySeason}
        />
      </div>
    );
  }
}
