$('#fm').on('submit', function(e){
	e.preventDefault();
	window.location = window.location + $('#zipcode').val();
});