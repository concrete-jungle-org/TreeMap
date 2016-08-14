let alt = require('../alt');
import { browserHistory } from 'react-router';

let ServerSetting = require('./../../setting/server.json');
let PersonSource = require('./../sources/person.source');
import { localization } from './../utils/localization';
import { displaySuccessMessage, displayFailMessage } from './../message/popup.component';

class PersonActions {
  setCode(code) {
    return (dispatch) => {
      dispatch(code);
    }
  }
  fetchUser(id) {
    return (dispatch) => {
      dispatch();
      PersonSource.fetchPersons([id]).then((response) => {
        this.fetchedUser(response);
      }).catch((code) => {
        if (__DEV__) {
          console.error(localization(code));
        }
        this.setCode(code);
      });
    }
  }
  fetchedUser(props) {
    return (dispatch) => {
      dispatch(props);
    }
  }
  fetchPersons(ids) {
    return (dispatch) => {
      dispatch();
      PersonSource.fetchPersons(ids).then((response) => {
        this.fetchedPersons(response);
      }).catch((code) => {
        if (__DEV__) {
          console.error(localization(code));
        }
        this.setCode(code);
      });
    }
  }
  fetchedPersons(props) {
    return (dispatch) => {
      dispatch(props);
    }
  }
  createTempPerson() {
    return (dispatch) => {
      dispatch();
    }
  }
  createPerson(person) {
    return (dispatch) => {
      dispatch();
      this.setCode(93);
      PersonSource.createPerson(person).then((response) => {
        displaySuccessMessage(localization(905));
        this.createdPerson(response);
      }).catch((code) => {
        displayFailMessage(localization(code));
        if (__DEV__) {
          console.error(localization(code));
        }
        this.setCode(code);
      });
    }
  }
  createdPerson(props) {
    return (dispatch) => {
      dispatch(props);
    }
  }
}

module.exports = alt.createActions(PersonActions);
