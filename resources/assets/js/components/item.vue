<template>
	<div class="row">
		<div class="col-sm-6">
			<div class="row">
				<div class="col-xs-5 text-right">
					<img v-if="item.photo" v-bind:src="item.photo">
				</div>
				<div class="col-xs-7">
					<p class="name">
						<a v-if="item.website" href="{{ item.website }}">{{ item.name }} {{ party }}</a>
						<span v-else>{{ item.name}} {{ party }}</span>
					</p>
					<p class="office">{{ item.office }}</p>
					<p v-if="phone"><strong>Telephone:</strong> {{ phone }}</p>
					<address v-if="address"><strong>Address:</strong><br>{{{ address }}}</address>
				</div>
			</div>
		</div>
		<div class="col-sm-6 item-links">
			<p v-if="item.website"><a href="{{ item.website }}"><i class="fa fa-desktop"></i> Homepage</a></p>
			<p v-if="item.contact_form"><a href="{{ item.contact_form }}"><i class="fa fa-envelope"></i> Email</a></p>
			<p v-if="item.email"><a href="mailto:{{ item.email }}"><i class="fa fa-envelope"></i> Email</a></p>
			<p v-if="item.facebook_id"><a href="http://facebook.com/{{ item.facebook_id }}"><i class="fa fa-facebook-official"></i> Facebook.com/{{ item.facebook_id }}</a></p>
			<p v-if="item.twitter_id"><a href="http://twitter.com/{{ item.twitter_id }}"><i class="fa fa-twitter"></i> @{{ item.twitter_id }}</a></p>
			<p v-if="item.google_id"><a href="http://plus.google.com/{{ item.google_id }}"><i class="fa fa-google-plus"></i> {{ item.google_id }}</a></p>
			<p v-if="item.youtube_id"><a href="http://youtube.com/{{ item.youtube_id }}"><i class="fa fa-youtube"></i> {{ item.youtube_id }}</a></p>
			<p v-if="role"><a href="/edit/{{ item._id }}"><i class="fa fa-flag"></i> Edit</a></p>
			<p v-else><a v-on:click="flag" href="#"><i class="fa fa-flag"></i> Report Broken Link</a></p>
		</div>
	</div>
</template>

<script>
	import request from 'superagent';

	export default {
		name: 'Item',
		props: {
			item: Object,
			role: Boolean
		},
		computed: {
			district() {
				return this.item.district ? 'District ' + this.item.district : '';
			},
			party() {
				return this.item.party ? '[' + this.item.party[0] + ']' : '';
			},
			address() {
				let a = this.item.address;
				if (typeof a == "string")
					return a;
				if (Array.isArray(a))
					return a.join('<br>');
				return '';
			},
			phone() {
				let phone = '';
				if (!phone && !phones)
					return '';
				if (typeof this.item.phone == "string")
					phone = this.item.phone;
				else if (Array.isArray(this.item.phones))
					phone = this.item.phones[0];
				phone = phone.replace('(', '').replace(') ', '-');
				return phone;
			}
		},
		methods: {
			flag(e) {
				e.preventDefault();
		    	request('/rep/' + this.item._id + '/flag', (err, res) => {});
				$(e.target).replaceWith('<p>Thanks!</p>');
			}
		}
	};
</script>

<style>
	.results img {
		margin: 0 auto;
		width: 180px;
		max-width: 100%;
	}
	.results .row {
		padding-top: 10px;
		padding-bottom: 10px;
	}
	.results .row:nth-of-type(even) {
		background: #DEE1EA;
	}
	.name {
		font-size: 20px;
		font-weight: bold;
		margin-bottom: 5px;
	}
	.office {
		font-size: 18px;
	}
</style>