import Vue from 'vue';
import QueryView from './components/QueryView.vue';
import AboutView from './components/AboutView.vue';
import ContactView from './components/ContactView.vue';
import TermsView from './components/TermsView.vue';
import store from './store';

Vue.config.debug = true;

Vue.component('about', AboutView);
Vue.component('query', QueryView);
Vue.component('contact', ContactView);
Vue.component('terms', TermsView);

Vue.filter('search', {
	read: function(val){
		return '';
	},
	write: function(val, oldVal){
		return val;
	}
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var vm = new Vue({
	el: '#app',
	data: {
		currentView: '',
		query: '',
		geolocation: null,
		$input: null,
		pages: [
			'about', 'contact', 'terms'
		]
	},
	events: {
		'message-sent': function(){
			this.page('');
		}
	},
	watch: {
		query: function(val){
			console.log('main js new query: ' + val);
			this.currentView = 'query';
		},
		currentView: function(val){
			console.log('main js new view: ' + val);
			if (val == 'query'){
			}
			this.isPopState = false;
		}
	},
	created() {
		this.$input = $('#input', this.$el).focus();
		this.init();
		window.onpopstate = e => {
			this.isPopState = true;
			this.init();
		};
	},
	methods: {
		init() {
			let path = window.location.pathname.substr(1);
			if (this.pages.indexOf(path) !== -1){
				this.currentView = path;
				document.title = 'Contact My Reps - ' + path.charAt(0).toUpperCase() + path.slice(1);
			}else{
				this.currentView = 'query';
				this.query = path;
				document.title = this.query != '' ? 'Contact My Reps - ' + this.query : 'Contact My Representatives';
			}
		},
		locate() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition( position => {
					this.geolocation = position.coords;
					this.query = this.geolocation.latitude+'/'+this.geolocation.longitude;
					document.title = 'Contact My Reps - My Location';
					history.replaceState({}, 'ContactMyReps - My Location', '/');
				}, () => {
					this.status = 'You must accept location permissions to use your current location.';
				});
			} else {
				this.status = 'Your browser does not support geolocation';
			}
		},
		search(e) {
			this.query = this.$input.val();
			document.title = 'Contact My Reps - ' + this.query;
			history.pushState({}, 'ContactMyReps - ' + this.query, '/' + this.query);
			this.$input.focus();
		},
		page(page) {
			if (window.location.pathname.substr(1) == page){
				return;
			}

			if (this.pages.indexOf(page) != -1){
				this.currentView = page;
				document.title = 'Contact My Reps - ' + page.charAt(0).toUpperCase() + page.slice(1);
				history.pushState({}, page, '/'+page);
				return;
			}

			this.query = page;
			this.currentView = 'query';
			if (this.query == ''){
				document.title = 'Contact My Reps';
				history.pushState({}, 'ContactMyReps', '/');
			}else{
				document.title = 'Contact My Reps - ' + this.query;
				history.pushState({}, 'ContactMyReps - ' + this.query, '/' + this.query);
			}

		},
	}
});