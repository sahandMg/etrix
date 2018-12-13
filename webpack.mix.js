let mix = require('laravel-mix');
require('laravel-mix-auto-extract');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.react('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');

mix.autoExtract();
// mix.extract();
// mix.webpack.mergeConfig({
//     babel: {
//         presets: ['es2015','stage-0','react'],
//         plugins: [
//             require('rollup-plugin-replace')({
//                 'process.env.NODE_ENV': JSON.stringify('production')
//             }),
//             require('rollup-plugin-commonjs')(),
//             require('rollup-plugin-uglify')(),
//         ],
//     }
// });
