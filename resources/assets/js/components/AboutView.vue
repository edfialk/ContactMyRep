<template>
	<div class="container"></div>
</template>

<script>
import store from '../store';

export default {
	name: 'About',
	created() {
		var self = this;
		store.fetchPage('about').then( function(val){
			self.$el.innerHTML = val;
		});
	}

}

</script>