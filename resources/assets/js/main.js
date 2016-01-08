import Vue from 'vue';
import request from 'superagent';

import Item from './components/item.vue';

Vue.config.debug = true;

var gpsRegex = /^[-+]?([1-8]?\d(.\d+)?|90(.0+)?)[,\/]\s*[-+]?(180(.0+)?|((1[0-7]\d)|([1-9]?\d))(.\d+)?)$/;

Vue.filter('search', {
	read: function(val){
		return '';
	},
	write: function(val, oldVal){
		return val;
	}
});
/*		if (val.match(gpsRegex) != null){
			if (val.indexOf(',') != -1){
				val = val.split(',');
			}else{
				val = val.split('/');
			}
			return val[0] + ', ' + val[1];
		}
		return val;*/

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
		},
		'status': function(val){
/*			setTimeout(function(vm){
				console.log('status watch: ' + status);
				vm.status = '';
			}, 5000, this);*/
		}
	},
	created() {
		var path = window.location.pathname;
		if (path.length > 1){ //root is '/'
			this.query = path.substr(1);
			this.fetch();
		}else{
			var gps = document.getElementById('gps');
			if (gps){
				gps = gps.value.split(',');
				this.gps.lat = gps[0];
				this.gps.lng = gps[1];
				this.query = this.gps.lat + '/' + this.gps.lng;
				this.fetch();
			}
		}
	},
	methods: {
		search(event) {
			event.preventDefault();
			this.fetch();
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
				}, () => {
					this.status = 'You must accept location permissions to use your current location.';
				});
			} else {
				this.status = 'Your browser does not support geolocation';
			}
		},
	},
});

