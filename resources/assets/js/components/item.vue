<template>
	<tr>
		<td><img v-if="item.photo" v-bind:src="item.photo"></td>
		<td><a href='/rep/{{ item._id }}'>{{ item.name }} {{ party }}</a></td>
		<td>{{ item.office }}</td>
		<td>{{ phone }}</td>
		<td>{{{ address }}}</td>
		<td>
			<a v-if="item.website" href="{{ item.website }}"><i class="fa fa-desktop"></i></a>
			<a v-if="item.contact_form" href="{{ item.contact_form }}"><i class="fa fa-envelope"></i></a>
			<a v-if="item.email" href="mailto:{{ item.email }}"><i class="fa fa-envelope"></i></a>
			<a v-if="item.facebook_id" href="http://facebook.com/{{ item.facebook_id }}"><i class="fa fa-facebook-official"></i></a>
			<a v-if="item.twitter_id" href="http://twitter.com/{{ item.twitter_id }}"><i class="fa fa-twitter"></i></a>
			<a v-if="item.google_id" href="http://plus.google.com/{{ item.google_id }}"><i class="fa fa-google-plus"></i></a>
			<a v-if="item.youtube_id" href="http://youtube.com/{{ item.youtube_id }}"><i class="fa fa-youtube"></i></a>
			<a v-if="role" href="/edit/{{ item._id }}"><i class="fa fa-flag"></i></a>
		</td>
	</tr>
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
		max-width: 100px;
	}
</style>