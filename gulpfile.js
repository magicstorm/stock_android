var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss');


mix.styles([
        'master.css',
        'bootstrap.min.css',
    ], 'public/css/master.css', 'resources/assets/css');

mix.scripts([
        'master.js',
        'jquery-2.2.3.min.js',
        'bootstrap.min.js',
        //'modernizr-2.6.2.min.js',
    ], 'public/js/master.js', 'resources/assets/js');

});
