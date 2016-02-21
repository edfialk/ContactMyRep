$(document).ready(function(){

	$('form').delegate('.btn-add','click',function(e){
		var $group = $(e.target).parents('.input-group');
		var $clone = $group.clone();
		$clone.find('input').removeAttr('value');
		$clone.find('.btn-add').toggleClass('btn-add btn-minus');
		$clone.find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
		$group.parent().append($clone);
	});

	$('form').delegate('.btn-minus','click',function(e){
		$(e.target).parents('.input-group').remove();
	});

});