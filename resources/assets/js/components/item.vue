<template>
	<div class="row">
		<div class="col-xs-2 text-center">
			<img v-if="item.photo" v-bind:src="item.photo">
		</div>
		<div class="col-xs-4">
			<p class="name"><a href='/rep/{{ item._id }}'>{{ item.name }} {{ party }}</a></p>
			<p class="office">{{ item.office }}</p>
			<p v-if="phone">Office Phone: {{ phone }}</p>
			<address v-if="address">Address:<br>{{{ address }}}</address>
		</div>
		<div class="col-xs-6 item-links">
			<p v-if="item.website"><a href="{{ item.website }}"><i class="fa fa-desktop"></i> Homepage</a></p>
			<p v-if="item.contact_form"><a href="{{ item.contact_form }}"><i class="fa fa-envelope"></i> Email</a></p>
			<p v-if="item.email"><a href="mailto:{{ item.email }}"><i class="fa fa-envelope"></i> Email</a></p>
			<p v-if="item.facebook_id"><a href="http://facebook.com/{{ item.facebook_id }}"><i class="fa fa-facebook-official"></i> Facebook.com/{{ item.facebook_id }}</a></p>
			<p v-if="item.twitter_id"><a href="http://twitter.com/{{ item.twitter_id }}"><i class="fa fa-twitter"></i> @{{ item.twitter_id }}</a></p>
			<p v-if="item.google_id"><a href="http://plus.google.com/{{ item.google_id }}"><i class="fa fa-google-plus"></i> {{ item.google_id }}</a></p>
			<p v-if="item.youtube_id"><a href="http://youtube.com/{{ item.youtube_id }}"><i class="fa fa-youtube"></i> {{ item.youtube_id }}</a></p>
			<p v-if="role"><a href="/edit/{{ item._id }}"><i class="fa fa-flag"></i> Edit</a></p>
			<p v-else><a href="/rep/{{ item._id }}/flag"><i class="fa fa-flag"></i> Report for review</a></p>
		</div>
	</div>
</template>

<script>
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
				if (!this.item.address)
					return '';
				if (typeof this.item.address == "string")
					return this.item.address;
				return this.item.address.join('<br>');
			},
			phone() {
				var phone = '';
				if (Array.isArray(this.item.phones))
					phone = this.item.phones[0];
				else if (typeof this.item.phone == "string")
					phone = this.item.phone;
				phone = phone.replace('(', '');
				phone = phone.replace(') ', '-');
				return phone;
			}
		}
	};
</script>

<style>
	img {
		max-width: 100%;
		margin: 0 auto;
	}
	.results .row {
		padding-top: 10px;
		padding-bottom: 10px;
	}
	.results .row:nth-of-type(odd) {
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