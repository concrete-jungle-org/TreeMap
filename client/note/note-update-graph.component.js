import $ from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import AltContainer from 'alt-container';
import moment from 'moment';

require('./note-graph.component.scss');
var FontAwesome = require('react-fontawesome');
let ServerSetting = require('./../../setting/server.json');

import { localization } from './../utils/localization';
import NoteLine from './../note/note-line.component';
import { sortNoteByDateASC } from './../utils/sort';
import { NOTETYPE } from './../utils/enum';
let TreeActions = require('./../actions/tree.actions');
let TreeStore = require('./../stores/tree.store');
let AuthStore = require('./../stores/auth.store');
import { google10Color } from './../utils/color';


export default class NoteUpdateGraph extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.notes = [];
    this.drawTimer;
  }
  componentWillMount() {
    this.setState({width: 0, height: 0, legend: null});
  }
  componentDidMount () {
    $(window).resize(function() {
      this.updateCanvasSize(true);
    }.bind(this));
    this.updateCanvasSize(false);
  }
  componentWillReceiveProps(nextProps) {
    this.renderGraph(nextProps, false);
  }
  componentWillUnmount() {
    $(window).off("resize");
    if (this.drawTimer) {
      clearTimeout(this.drawTimer);
    }
  }
  updateCanvasSize(first) {
    let wrapper = ReactDOM.findDOMNode(this.refs['wrapper']);
    this.setState({width: wrapper.clientWidth, height: ServerSetting.iTreeRatingGraphHeight});
    setTimeout(function() {
      this.renderGraph(this.props, first);
    }.bind(this));
  }
  renderGraph(props, first) {
    if (this.drawTimer) {
      clearTimeout(this.drawTimer);
    }
    this.drawTimer = setTimeout(function() {
      let rendering = false;
      if (this.notes && this.notes.length == 0) {
        rendering = true;
      } else if (this.notes.length != props.notes.length) {
        rendering = true;
      } else {
        let bFound = false;
        for (let i = 0; i < props.notes.length && !bFound; i++) {
          if (this.notes[i].id != props.notes[i].id || this.notes[i].rate != props.notes[i].rate) {
            bFound = true;
          }
        }
        if (bFound) {
          rendering = true;
        }
      }
      this.notes = props.notes.slice();
      if ((first || rendering) && props.notes) {
        console.log("Drawing an update chart.");
        this.ready = false; // set ready flag as false so that it doesn't render twice.
        let rCanvas = ReactDOM.findDOMNode(this.refs['canvas-update']);
        let ctx = rCanvas.getContext("2d");

        let lists = [[]];
        let notes = props.notes.sort(sortNoteByDateASC);
        let currentYear = moment(new Date()).year();
        let earlestYear;
        let latestYear;
        let max;
        let min;
        if (notes.length == 0) {
          earlestYear = currentYear;
          latestYear = currentYear;
        } else {
          earlestYear = moment(notes[0].date).year();
          latestYear = moment(notes[notes.length - 1].date).year();
          for (let j = 0; j < notes.length; j++) {
            for (let i = earlestYear; i <= latestYear; i++) {
              if (notes[j].date.year() == i) {
                if (notes[j].type == NOTETYPE.UPDATE && notes[j].rate >= 3) {
                  if (lists[i - earlestYear] == null) {
                    lists[i - earlestYear] = [];
                  }
                  lists[i - earlestYear].push({x: moment(notes[j].date).year(currentYear).toDate(), y: notes[j].rate, r: 1});
                }
              }
            }
          }
        }
        let data = [];
        for (let i = 0; i < lists.length; i++) {
          data.push({
            label: i + earlestYear,
    				strokeColor: google10Color(i + earlestYear),
    				data: lists[i] != null ? lists[i] : [],
          });
        }
        let chart = new Chart(ctx).Scatter(data, {
          bezierCurve: false,
          bezierCurveTension: 0.3,
  				showTooltips: true,
  				scaleShowHorizontalLines: true,
  				scaleShowLabels: true,
  				scaleType: "date",
          scaleLabel: "<% if (value <= 5) { %>★x<%=value%><% } %>",
          // Boolean - If we want to override with a hard coded y scale
          scaleOverride: true,
          // ** Required if scaleOverride is true **
          // Number - The number of steps in a hard coded y scale
          scaleSteps: 2,
          // Number - The value jump in the hard coded y scale
          scaleStepWidth: 1,
          // Number - The y scale starting value
          scaleStartValue: 3,
          // Interpolated JS string - can access point fields:
          // argLabel, valueLabel, arg, value, datasetLabel, size
          scaleDateTimeFormat: "mmm dd, ",
          tooltipTemplate: "<%=argLabel%><%if (datasetLabel){%><%=datasetLabel%><%}%>: <%=valueLabel%>",

          // Interpolated JS string - can access point fields:
          // argLabel, valueLabel, arg, value, datasetLabel, size
          multiTooltipTemplate: "<%=argLabel%><%if (datasetLabel){%><%=datasetLabel%><%}%>: <%=valueLabel%>",

          // Interpolated JS string - can access all chart fields
          legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><%for(var i=0;i<datasets.length;i++){%><li><span class=\"<%=name.toLowerCase()%>-legend-marker\" style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%=datasets[i].label%></li><%}%></ul>"
        });
        this.setState({legend: chart.generateLegend()});
      }
    }.bind(this), 1000);
  }
  render () {
    let canvasStyle = {width: this.state.width, height: this.state.height};
    return (
      <div ref="wrapper" className="note-graph-wrapper">
        <div className="note-graph-label"><FontAwesome className='' name='star' /> {localization(36)}</div>
        <canvas ref="canvas-update" className="update-graph-canvas" style={canvasStyle} />
      </div>
    );
  }
}
//<div dangerouslySetInnerHTML={{__html: this.state.legend}} />
