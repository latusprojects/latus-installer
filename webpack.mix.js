const mix = require('laravel-mix');

let outputDir = 'resources/assets/dist';

mix.setPublicPath(outputDir);

mix.js('resources/assets/js/installer.js', '')
    .sass('resources/assets/css/installer.scss', '');

if (mix.inProduction()) {
    mix.version();
}