import React from 'react';
import AltContainer from 'alt-container';

require('./tree-info.component.scss');
var FontAwesome = require('react-fontawesome');
let ServerSetting = require('./../../setting/server.json');

import { localization } from './../utils/localization';
import TreeFood from './tree-food.component';
import TreeLocation from './tree-location.component';
import TreeAddress from './tree-address.component';
import TreeDescription from './tree-description.component';
import TreeFlag from './tree-flag.component';
import TreeOwnership from './tree-ownership.component';

let TreeActions = require('./../actions/tree.actions');
let TreeStore = require('./../stores/tree.store');
let AuthStore = require('./../stores/auth.store');


export default class TreeInfo extends React.Component {
  constructor(props, context) {
    super(props, context);
  }
  componentWillMount() {
    this.setState({selected: TreeStore.getState().selected, editing: false, editable: AuthStore.getState().auth.canEditTree(TreeStore.getState().selected)});
  }
  componentDidMount () {

  }
  componentWillReceiveProps(nextProps) {
    if (TreeStore.getState().selected != this.state.selected) {
      this.setState({selected: TreeStore.getState().selected, editing: false, editable: AuthStore.getState().auth.canEditTree(TreeStore.getState().selected)});
    }
  }
  render () {
    let actions;
    if (this.state.editable) {
      actions = <div>
        <div className="solid-button-group">
          <div className="solid-button solid-button-green" onClick={() => {
            this.setState({editing: true});
            TreeActions.setEditing(TreeStore.getState().selected, true);
          }}>
            {localization(928) /* EDIT */}
          </div>
        </div>
      </div>;
    }
    if (this.state.editing) {
      if (AuthStore.getState().auth.isRecentlyAddedByUser(this.state.selected)) {  // Delete option is only available for a newly added tree.
        actions = <div>
          <div className="solid-button-group">
            <div className="solid-button solid-button-green" onClick={() => {
              TreeActions.updateTree(TreeStore.getState().temp);
              this.setState({editing: false});
            }}>
              {localization(930) /* SAVE */}
            </div>
            <div className="solid-button solid-button-green" onClick={() => {
              TreeActions.setSelected(TreeStore.getState().selected);
              this.setState({editing: false});
            }}>
              {localization(933) /* CANCEL */}
            </div>
          </div>
          <div className="danger-zone">{localization(927) /* DELETE THIS TREE */}</div>
          <div className="solid-button-group">
            <div className="solid-button solid-button-red" onClick={() => {
              this.context.router.push({pathname: window.location.pathname, hash: "#delete"});
            }}>
              {localization(965) /* DELETE THIS TREE */}
            </div>
          </div>
        </div>;
      } else {
        actions = <div>
          <div className="solid-button-group">
            <div className="solid-button solid-button-green" onClick={() => {
              TreeActions.updateTree(TreeStore.getState().temp);
              this.setState({editing: false});
            }}>
              {localization(930) /* SAVE */}
            </div>
            <div className="solid-button solid-button-green" onClick={() => {
              TreeActions.setSelected(TreeStore.getState().selected);
              this.setState({editing: false});
            }}>
              {localization(933) /* CANCEL */}
            </div>
          </div>
          <div className="danger-zone">{localization(927) /* THIS TREE IS DEAD */}</div>
          <div className="solid-button-group">
            <div className="solid-button solid-button-red" onClick={() => {
              this.context.router.push({pathname: window.location.pathname, hash: "#dead"});
            }}>
              {localization(1001) /* THIS TREE IS DEAD */}
            </div>
          </div>
        </div>;
      }
    }
    let info;
    if (this.state.editable) {
      info = <AltContainer stores={
        {
          tree: function(props) {
            return {
              store: TreeStore,
              value: TreeStore.getState().temp
            }
          }
        }
      }>
        <TreeFood editing={this.state.editing} />
        <TreeLocation editing={this.state.editing} />
        <TreeAddress editing={this.state.editing} />
        <TreeDescription editing={this.state.editing} />
        <TreeOwnership editing={this.state.editing} />

      </AltContainer>
    } else {
      info = <AltContainer stores={
        {
          tree: function(props) {
            return {
              store: TreeStore,
              value: TreeStore.getState().temp
            }
          }
        }
      }>
        <TreeFood editing={false} />
        <TreeLocation editing={false} />
        <TreeAddress editing={false} />
        <TreeDescription editing={false} />
      </AltContainer>
    }
    return (
      <div className="tree-info-wrapper">
        {info}
        {actions}
      </div>
    );
  }
}

TreeInfo.contextTypes = {
    router: React.PropTypes.object.isRequired
}
// <TreeFlag editing={this.state.editing} />
