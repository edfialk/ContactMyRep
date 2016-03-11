<template>
	<div class="container"></div>
</template>

<script>
import store from '../store';

export default {
	name: 'Terms of Service',
	created() {
		var self = this;
		store.fetchPage('terms').then( function(val){
			self.$el.innerHTML = val;
		});
	}

}

</script>