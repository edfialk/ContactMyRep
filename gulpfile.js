var elixir = require('laravel-elixir');

require('laravel-elixir-vueify');

elixir(function(mix) {
	mix.copy('node_modules/sweetalert/dist/sweetalert.css', 'resources/assets/sass/sweetalert.css');
    mix.sass('app.scss')
    	.browserify('main.js')
    	.browserify('edit.js')
    ;
});
