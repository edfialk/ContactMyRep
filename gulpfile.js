var elixir = require('laravel-elixir');
require('laravel-elixir-vueify');

elixir(function(mix) {
    mix.sass('app.scss')
       .browserify('home.js')
       .browserify('edit.js')
    ;
});
