(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

$(document).ready(function () {

	$('form').delegate('.btn-add', 'click', function (e) {
		var $group = $(e.target).parents('.input-group');
		var $clone = $group.clone();
		$clone.find('input').val('');
		$clone.find('.btn-add').toggleClass('btn-add btn-minus');
		$clone.find('.glyphicon').toggleClass('glyphicon-plus glyphicon-minus');
		$group.parent().append($clone);
	});

	$('form').delegate('.btn-minus', 'click', function (e) {
		$(e.target).parents('.input-group').remove();
	});
});

},{}]},{},[1]);

//# sourceMappingURL=edit.js.map
