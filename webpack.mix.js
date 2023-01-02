let mix = require('laravel-mix');

mix.setPublicPath('./');

mix.sass('resources/css/tom-select.scss', 'assets/css');
mix.js('resources/js/tom-select.js', 'assets/js');
mix.js('resources/js/tom-select-init-frontend.js', 'assets/js');

mix.version();
