<template>
	<tr>
		<td class="center-align"><img v-if="item.photo" src="{{ item.photo }}"></td>
		<td><a href='#'>{{ item.name }} {{ party }}</a></td>
		<td>{{ item.office }}</td>
		<td>{{ item.phone ? item.phone : '' }}</td>
		<td>{{{ address }}}</td>
		<td>
			<a v-if="item.website" href="{{ item.website }}"><i class="fa fa-desktop"></i></a>
			<a v-if="item.contact_form" href="{{ item.contact_form }}"><i class="fa fa-envelope"></i></a>
			<a v-if="item.email" href="mailto:{{ item.email }}"><i class="fa fa-envelope"></i></a>
			<a v-if="item.facebook_id" href="http://facebook.com/{{ item.facebook_id }}"><i class="fa fa-facebook-official"></i></a>
			<a v-if="item.twitter_id" href="http://twitter.com/{{ item.twitter_id }}"><i class="fa fa-twitter"></i></a>
			<a v-if="item.google_id" href="http://plus.google.com/{{ item.google_id }}"><i class="fa fa-google-plus"></i></a>
			<a v-if="item.youtube_id" href="http://youtube.com/{{ item.youtube_id }}"><i class="fa fa-youtube"></i></a>
		</td>
	</tr>
</template>

<script>
	export default {
		name: 'Item',
		props: {
			item: Object
		},
		computed: {
			district() {
				return this.item.district ? 'District ' + this.item.district : '';
			},
			party() {
				return this.item.party ? '[' + this.item.party[0] + ']' : '';
			},
			address() {
				if (!this.item.address) return '';
				if (typeof this.item.address == "string") return this.item.address;
				var pieces = [];
				if (this.item.address.line1) pieces.push(this.item.address.line1);
				if (this.item.address.line2) pieces.push(this.item.address.line2);
				if (this.item.address.line3) pieces.push(this.item.address.line3);
				var street = pieces.join('<br>');
				var city = this.item.address.city.toLowerCase().replace( /\b\w/g, function (m) {
            		return m.toUpperCase();
        		});

				return street + '<br>' + city + ', ' + this.item.address.state + ' ' + this.item.address.zip;
			}
		}
	};
</script>

<style>
	img {
		max-width: 100px;
	}
</style>