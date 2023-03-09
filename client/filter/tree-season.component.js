import React from 'react';
require('./tree-food.component.scss');

const TreeSeason = ({isValid, firstWeek, lastWeek, updateFirstWeek, updateLastWeek, resetFoodsFilter, filterBySeason}) => {
  return (
    <div className="tree-season-wrapper">
      <div className="solid-button-group">
        <div className="solid-button solid-button-green" onClick={() => {
          resetFoodsFilter();
        }}>
          Reset
        </div>
      </div>
      <div className="solid-button-group week-selector">
        <label>From Week: 
          <input
            value={firstWeek}
            min={1}
            max={52}
            onChange={e => updateFirstWeek(e.target.value)}
            type="number"
            />
        </label>
        <label>To Week: 
          <input
            value={lastWeek}
            min={2}
            max={53}
            onChange={e => updateLastWeek(e.target.value)}
            type="number"
            />
        </label>
      </div>
      <div className="solid-button-group">
        {
          isValid ?
          <div className="solid-button solid-button-green" onClick={() => {
            filterBySeason();
          }}>
            Filter In Season Weeks
          </div>
          :
          <div><p className="warning-msg">Invalid range</p></div>
        }
      </div>
    </div>
  )
}

export default TreeSeason;
