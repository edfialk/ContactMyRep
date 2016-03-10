import Vue from 'vue';
import request from 'superagent';
import Item from './components/item.vue';

Vue.config.debug = true;
/*Vue.filter('search', {
	read: function(val){
		return '';
	},
	write: function(val, oldVal){
		return val;
	}
});*/

var vm = new Vue({
	el: '#home',
	components: {
		Item,
	},
	data: {
		loading: false,
		query: null,
		queryType: null,
		location: null,
		reps: [],
		districts: [],
		status: '',
		apiroot: '/api/v1/',
		role: null,
		$results: null
	},
	computed: {
		hasResults() {
			return this.reps.length > 0;
		},
		printSearch() {
			let l = this.location;
			let base = 'Search Results for ';
			if (!l)
				return base + decodeURIComponent(this.query);
			if (l.city && l.state_name)
				return base + l.city + ', ' + l.state_name;
			if (l.address && l.zip)
				return base + l.address + ', ' + l.zip;
			if (l.zip && l.state_name)
				return base + l.zip + ' - ' + l.state_name;
			if (l.state_name)
				return base + l.state_name;
			return '';
		}
	},
	created() {
		window.onpopstate = this.init;
		this.init();
	},
	methods: {
		init() {
			this.$results = $('.results');
			if (this.getUrlQuery().length > 1){ //root is '/'
				this.query = this.getUrlQuery()
				this.queryType = "search";
				this.fetch();
			}else if (typeof ipinfo !== "undefined" && ipinfo.loc){
				let gps = ipinfo.loc.split(',');
				this.query = gps[0] + '/' + gps[1];
				this.fetch();
			}
			this.role = document.getElementById('role') !== null;
		},
		search(e) {
			e.preventDefault();
			this.queryType = "search";
			this.fetch();
			history.pushState({}, '', '/' + this.query);
		},
		fetch() {
		    this.status = '';
		    this.loading = true;
		    this.$results.css('display', 'none');
		    this.reps = [];
		    request(this.apiroot + this.query, (err, res) => {
		        this.loading = false;

		    	if (err){
		    		this.status = err.message;
		    		return;
		    	}

		        let body = res.body;
		    	if (body.status == "error"){
		    		this.status = body.message;
		    		return;
		    	}

		        this.reps = body.reps;
		        this.location = body.location;
		        this.$results.fadeIn();
		    });
		},
		locate() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition( position => {
					let {latitude, longitude} = position.coords;
					this.query = latitude+'/'+longitude;
					this.queryType = "gps";
			  		history.pushState({}, '', '/'+this.query);
					this.fetch();
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

