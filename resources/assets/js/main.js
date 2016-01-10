import Vue from 'vue';
import request from 'superagent';

import Item from './components/item.vue';

Vue.config.debug = true;

Vue.filter('search', {
	read: function(val){
		return '';
	},
	write: function(val, oldVal){
		return val;
	}
});

var vm = new Vue({
	el: '.container',
	components: {
		Item,
	},
	data: {
		loading: false,
		query: null,
		zip: null,
		city: null,
		state: null,
		gps: {
			lat: null,
			lng: null
		},
		reps: [],
		districts: [],
		status: '',
		apiroot: '/api/v1/',
	},
	watch: {
		'gps': function(val, oldVal){
			console.log('gps watch');
			if (val.lat && val.lng){
				this.query = val.lat+'/'+val.lng;
				this.fetch();
			}
		}
	},
	created() {
		window.onpopstate = function(e){
			this.init();
		}.bind(this);
		this.init();
	},
	methods: {
		init() {
			if (this.getUrlQuery().length > 1){ //root is '/'
				this.query = this.getUrlQuery()
				this.fetch();
			}else{
				var gps = document.getElementById('gps');
				if (gps){
					gps = gps.value.split(',');
					this.gps.lat = gps[0];
					this.gps.lng = gps[1];
					this.query = this.gps.lat + '/' + this.gps.lng;
					this.gpsFetch = true;
					this.fetch();
				}
			}
		},
		search(event) {
			event.preventDefault();
			this.gpsFetch = false;
			this.fetch();
			history.pushState({}, '', '/'+this.query);
		},
		fetch() {
			this.status = '';
			this.loading = true;
			console.log('fetching: ' + this.query);
			request.get(this.apiroot+this.query).end((req, resp) => {
				document.getElementById('input').value = '';
				this.loading = false;
				var body = resp.body;
				this.reps = body.reps;
				if (body.location){
					this.zip = body.location.zip;
					this.city = body.location.city;
					this.state = body.location.state;
				}
			});
		},
		locate() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition( position => {
					this.gps = {
				    	lat: position.coords.latitude,
				    	lng: position.coords.longitude
			  		};
			  		history.pushState({}, '', '/');
				}, () => {
					this.status = 'You must accept location permissions to use your current location.';
				});
			} else {
				this.status = 'Your browser does not support geolocation';
			}
		},
		getUrlQuery() {
			return window.location.pathname.substr(1);
		}
	},
});

