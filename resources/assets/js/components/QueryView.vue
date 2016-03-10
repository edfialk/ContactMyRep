<template>
	<div>
		<div class="row loading" v-show="loading" transition="fade">
			<div class="col-xs-12 text-center">
			</div>
		</div>
		<div class="row status" v-show="status">
			<div class="col-xs-12 text-center">
				<h4>
					<span v-text="status"></span>
				</h4>
			</div>
		</div>
		<div class="row location">
			<div class="col-xs-12 text-center">
				<h4>
					<span v-text="printSearch" v-show="!loading"></span>
				</h4>
			</div>
		</div>
		<div class="row results" v-show="!loading" transition="fade">
			<div class="col-xs-12">
				<item v-for="item in reps" :item="item" :role="role"></item>
			</div>
		</div>
	</div>
</template>

<script>
import Item from './Item.vue';
import store from '../store';
export default {

	name: 'HomeView',
	components: {
		Item
	},
	props: ['query'],
	data() {
		return {
			reps: [],
			status: '',
			loading: false,
			$results: null,
			location: null,
			role: null,
		}
	},
	computed: {
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
	watch: {
		query: function(val){
			if (val == '' || val == '/'){
				if (typeof ipinfo !== 'undefined' && ipinfo.loc){
					this.loading = true;
					let g = ipinfo.loc.split(',');
					store.fetch(g[0]+'/'+g[1]).then(this.handleResponse);
				}
				return;
			}
			this.loading = true;
			store.fetch(val).then(this.handleResponse);
		},
		loading: function(val){
			if (val){
				this.$results.fadeOut();
			}
		}
	},
	created() {
		this.loading = true;
		this.init();
	},
	methods: {
		init() {
			if (this.query === '' && typeof ipinfo !== 'undefined' && ipinfo.loc){
				let g = ipinfo.loc.split(',');
				store.fetch(g[0]+'/'+g[1]).then(this.handleResponse);
			}else if (this.query !== ''){
				store.fetch(this.query).then(this.handleResponse);
			}
			this.$results = $(this.el).find('.results');
			this.role = document.getElementById('role') !== null;
		},
		handleResponse(resp) {

			if (resp.status == "error" && resp.message){
				this.reps = [];
				this.status = resp.message;
				return;
			}

			this.status = '';
			this.reps = resp.reps;
			this.location = resp.location;
			this.loading = false;
		}
	}
}

</script>