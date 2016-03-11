<template>
	<div id="contact" class="container">
		<div class="row">
			<div class="col-md-9">
				<h2>CONTACT US</h2>
				<hr>
				<form v-on:submit.prevent="submit">
					<div class="form-group col-md-6">
						<label for="name">Name</label>
						<input type="text" v-model="name" class="form-control" id="name" placeholder="Name" required>
					</div>
					<div class="form-group col-md-6">
						<label for="email">Email address</label>
						<input type="email" v-model="email" class="form-control" id="email" placeholder="Email" required>
					</div>
					<div class="form-group col-xs-12">
						<label for="message">Message</label>
						<textarea v-model="message" class="form-control" rows="10" required></textarea>
					</div>
					<div class="form-group col-xs-12">
						<button type="submit" class="btn btn-block btn-primary" :disabled="mailsent">SEND</button>
					</div>
				</form>
			</div>
			<div class="col-md-3 sidebar-right">
				<div class="row">
					<div class="col-xs-12">
						<a href='http://facebook.com/contactmyreps'><img src='/images/fb-art.jpg'></a>
						<a href='http://twitter.com/contactmyreps'><img src='/images/twitter-art.png'></a>
						<a href="https://www.gofundme.com/cbtnqb5g" class="btn btn-success btn-lg">Donate</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

import swal from 'sweetalert';

export default {
	data() {
		return {
			name: '',
			email: '',
			message: '',
			status: '',
			mailsent: false,
		}
	},
	methods: {
		submit() {
			this.mailsent = true;
			$.post('/contact', {
				name: this.name,
				email: this.email,
				message: this.message
			});
			swal('Thanks!', 'Your message has been received.', 'success');
			this.$dispatch('message-sent');
		}
	}
}
</script>