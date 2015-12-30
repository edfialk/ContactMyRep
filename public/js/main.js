$('form').on('submit', function(e){
	e.preventDefault();
	window.location = '/' + $('#input').val();
});