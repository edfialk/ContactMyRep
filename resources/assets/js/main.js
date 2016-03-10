import Vue from 'vue';
import QueryView from './components/QueryView.vue';
import AboutView from './components/AboutView.vue';
import ContactView from './components/ContactView.vue';
import store from './store';

Vue.config.debug = true;

Vue.component('about', AboutView);
Vue.component('query', QueryView);
Vue.component('contact', ContactView);

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
		query: null,
		geolocation: null,
		$input: null,
	},
	created() {
		window.onpopstate = this.init;
		this.$input = $('#input');
		this.init();
	},
	methods: {
		init() {
			this.page(window.location.pathname.substr(1));
			this.$input.focus();
		},
		locate() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition( position => {
					this.geolocation = position.coords;
					this.query = this.geolocation.latitude+'/'+this.geolocation.longitude;
				}, () => {
					this.status = 'You must accept location permissions to use your current location.';
				});
			} else {
				this.status = 'Your browser does not support geolocation';
			}
		},
		search(e) {
			// this.currentView = 'query';
			this.page(this.$input.val());
			this.$input.focus();
		},
		page(page) {
			if (page == 'about' || page == 'contact'){
				this.currentView = page;
				document.title = 'Contact My Reps - ' + page.charAt(0).toUpperCase() + page.slice(1);
				history.pushState({}, page, page);
				return;
			}

			this.currentView = 'query';
			if (page == '/' || page == ''){
				document.title = 'Contact My Reps';
				history.pushState({}, 'ContactMyReps', '/');
				this.query = '';
				return;
			}

			history.pushState({}, page, '/' + page);
			document.title = 'Contact My Reps - ' + page;
			this.query = page;
		}
	}
});