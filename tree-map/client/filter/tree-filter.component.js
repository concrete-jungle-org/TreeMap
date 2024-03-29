import React from 'react';
import AltContainer from 'alt-container';

require('./tree-filter.component.scss');
var FontAwesome = require('react-fontawesome');
let ServerSetting = require('./../../setting/server.json');

import { localization } from './../utils/localization';
import { readFilter } from './../utils/filter';

let TreeActions = require('./../actions/tree.actions');
let TreeStore = require('./../stores/tree.store');
let AuthStore = require('./../stores/auth.store');
import TreeFood from './tree-food.component';
import TreeRate from './tree-rate.component';
import TreeAdopt from './tree-adopt.component';
import TreeFlag from './tree-flag.component';
import TreeOwnership from './tree-ownership.component';


export default class TreeFilter extends React.Component {
  constructor(props, context) {
    super(props, context);
  }
  componentWillMount() {
    this.setState({adopts: [], flags: [], foods: [], weeks: [], ownerships: [], rates: []});
    this.updateProps(this.props);
  }
  componentWillReceiveProps(nextProps) {
    this.updateProps(nextProps);
  }
  updateProps(props) {
    TreeActions.setSelected(null);
    readFilter(function(response) { // Resolve callback.
      let adopts;
      let weeks = response.weeks;
      if (response.adopt) {
        adopts = response.adopt.split(",").map(function(adopt) {
          return parseInt(adopt);
        });
      }
      let dead = parseInt(response.dead);
      let foods = response.foods;
      let ownerships = response.ownerships.split(",").map(function(ownership) {
        return parseInt(ownership);
      });
      let rates = response.rates.map(function(rate) {
        return parseInt(rate);
      });
      this.setState({adopts, dead, foods, weeks, ownerships, rates});
    }.bind(this), function(response) {  // Reject callback.

    }.bind(this));
  }
  render () {
    if (AuthStore.getState().auth.isManager()) {
      return (
        <div>
          <TreeFood foods={this.state.foods} weeks={this.state.weeks}/>
          <TreeRate rates={this.state.rates} />
          <TreeAdopt adopts={this.state.adopts} />
          <TreeOwnership ownerships={this.state.ownerships} />
        </div>
      );
    } else {
      return (
        <div>
          <TreeFood foods={this.state.foods} weeks={this.state.weeks}/>
          <TreeRate rates={this.state.rates} />
          <TreeAdopt adopts={this.state.adopts} />
        </div>
      );
    }
  }
}

TreeFilter.contextTypes = {
    router: React.PropTypes.object.isRequired
}

// <TreeFlag dead={this.state.dead} />
