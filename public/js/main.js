$('form').on('submit', function(e){
	e.preventDefault();
	window.location = '/' + $('#input').val();
});

function initMap(){
	var gps = $('#my-gps').val().split(',');
	var map = new google.maps.Map(document.getElementById('map'), {
	    center: {lat: parseFloat(gps[0]), lng: parseFloat(gps[1])},
    	zoom: 15
  	});
  	var marker = new google.maps.Marker({
    	position: {lat: parseFloat(gps[0]), lng: parseFloat(gps[1])},
    	map: map,
    	title: 'Drag Me'
  	});
}