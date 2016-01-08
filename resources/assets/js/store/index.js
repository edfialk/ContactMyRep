import Vue from 'Vue';
import request from 'superagent';

import { EventEmitter } from 'events'

const store = new EventEmitter();

export default store;

store.root = '/api/v1/';

store.state = {
	query: null,
	zip: null,
	lat: null,
	lng: null,
	state: null,
	districts: [],
	reps: []
};

store.fetch = function(callback) {
	console.log('fetching data');
	request.get(this.root+this.state.query).end((req, resp) => {
		this.state.reps = resp.body;
	});
};

store.setQuery = function(query) {
	this.state.query = query;
};